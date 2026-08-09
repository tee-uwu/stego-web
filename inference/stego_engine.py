import sys
import os
import types
import logging

# Suppress TensorFlow C++ log output and oneDNN warnings on STDERR
os.environ["TF_ENABLE_ONEDNN_OPTS"] = "0"
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
logging.getLogger("tensorflow").setLevel(logging.ERROR)

try:
    import absl.logging
    absl.logging.set_verbosity(absl.logging.ERROR)
except Exception:
    pass

# Workaround for Windows Winsock initialization failure [WinError 10106] in asyncio/wrapt
try:
    import _overlapped
except OSError as e:
    if getattr(e, "winerror", None) == 10106 or "10106" in str(e):
        dummy_overlapped = types.ModuleType("_overlapped")
        dummy_overlapped.NULL = 0
        dummy_overlapped.INVALID_HANDLE_VALUE = -1
        sys.modules["_overlapped"] = dummy_overlapped

import pickle
import numpy as np
from PIL import Image
from cryptography.fernet import Fernet

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
IMG_SIZE = 64
SECRET_BITS = IMG_SIZE * IMG_SIZE
HEADER_BITS = 16


def load_key(key_path=None):
    if key_path is None:
        key_path = os.path.join(BASE_DIR, "encryption_key.pkl")
    if not os.path.exists(key_path):
        key = Fernet.generate_key()
        with open(key_path, "wb") as f:
            pickle.dump(key, f)
        return key
    with open(key_path, "rb") as f:
        return pickle.load(f)


def load_image(path):
    img = Image.open(path).convert("RGB").resize((IMG_SIZE, IMG_SIZE))
    return np.array(img).astype("float32") / 255.0


def save_image(arr, path):
    arr = np.clip(arr, 0, 1) * 255.0
    Image.fromarray(arr.astype("uint8")).save(path)  # save as PNG (lossless)


def int_to_bits(value, num_bits=HEADER_BITS):
    return np.array([int(b) for b in format(value, f"0{num_bits}b")], dtype=np.float32)


def bits_to_int(bits):
    bits = (bits > 0.5).astype(int)
    return int("".join(str(b) for b in bits), 2)


def text_to_bits(text, cipher, capacity_bits=SECRET_BITS):
    encrypted = cipher.encrypt(text.encode())
    payload_bits = np.unpackbits(np.frombuffer(encrypted, dtype=np.uint8)).astype(np.float32)
    max_payload = capacity_bits - HEADER_BITS
    if len(payload_bits) > max_payload:
        raise ValueError(f"Message too long. Max ~{max_payload // 8 - 57} characters for this image size.")
    header = int_to_bits(len(payload_bits))
    full = np.zeros(capacity_bits, dtype=np.float32)
    full[:HEADER_BITS] = header
    full[HEADER_BITS:HEADER_BITS + len(payload_bits)] = payload_bits
    return full.reshape(1, IMG_SIZE, IMG_SIZE, 1)


def bits_to_text(bits_array, cipher):
    flat = bits_array.flatten()
    payload_len = bits_to_int(flat[:HEADER_BITS])
    payload = flat[HEADER_BITS:HEADER_BITS + payload_len]
    payload = (payload > 0.5).astype(np.uint8)
    byte_arr = np.packbits(payload)
    return cipher.decrypt(bytes(byte_arr)).decode()


def main():
    if len(sys.argv) < 2:
        print(__doc__)
        return

    # Import TensorFlow only when actually needed (keeps --help fast)
    from tensorflow import keras

    mode = sys.argv[1]
    key = load_key()
    cipher = Fernet(key)

    encoder_path = os.path.join(BASE_DIR, "encoder.h5")
    decoder_path = os.path.join(BASE_DIR, "decoder.h5")

    encoder = keras.models.load_model(encoder_path)
    decoder = keras.models.load_model(decoder_path)

    if mode == "encode":
        if len(sys.argv) != 5:
            print("Usage: python stego_engine.py encode <cover_image> \"<secret text>\" <output_path>")
            return
        cover_path, secret_text, output_path = sys.argv[2], sys.argv[3], sys.argv[4]

        cover = load_image(cover_path).reshape(1, IMG_SIZE, IMG_SIZE, 3)
        secret_bits = text_to_bits(secret_text, cipher)

        stego = encoder.predict([cover, secret_bits], verbose=0)[0]
        save_image(stego, output_path)
        print(f"Stego image saved to {output_path}")

    elif mode == "decode":
        if len(sys.argv) != 3:
            print("Usage: python stego_engine.py decode <stego_image>")
            return
        stego_path = sys.argv[2]

        stego = load_image(stego_path).reshape(1, IMG_SIZE, IMG_SIZE, 3)
        recovered_bits = decoder.predict(stego, verbose=0)[0]

        try:
            text = bits_to_text(recovered_bits, cipher)
            print("Recovered message:", text)
        except Exception as e:
            print("Could not recover a valid message. This can happen if the image")
            print("was compressed/resized after encoding (e.g. saved as JPEG).")
            print("Error detail:", e)

    else:
        print("Unknown mode. Use 'encode' or 'decode'.")


if __name__ == "__main__":
    main()
