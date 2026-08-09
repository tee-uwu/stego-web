# Image Steganography Web Application (CipherNet)

A deep learning image steganography application that embeds encrypted text messages inside images using a custom Keras neural network model and a Laravel web application interface.

---

## Repository Structure

```
.
├── ai-training/
│   └── CipherNet.ipynb          # Google Colab notebook for data prep and model training
├── inference/
│   ├── stego_engine.py          # Python inference bridge script (encode & decode)
│   ├── encoder.h5               # Trained Encoder neural network weights
│   ├── decoder.h5               # Trained Decoder neural network weights
│   ├── encryption_key.pkl       # Fernet key for symmetric text encryption
│   └── requirements.txt         # Python dependency list
├── web-app/                     # Laravel 12 web application codebase
│   ├── app/Http/Controllers/    # StegoController executing Python inference processes
│   ├── resources/views/         # Tailwind CSS Blade template view
│   ├── routes/web.php           # Encrypt and Decrypt HTTP routes
│   └── ...
├── docs/                        # Project documentation and final report
└── README.md                    # Project overview and setup guide
```

---

## Setup & Execution Guide

### 1. Prerequisites
- **Python 3.10+**
- **PHP 8.2+** & **Composer**
- **Node.js** & **npm**

### 2. Python Inference Setup
Navigate to the `inference/` directory and install required Python dependencies:

```bash
cd inference
python -m venv venv
# On Windows:
venv\Scripts\activate
# On Linux/macOS:
# source venv/bin/activate

pip install -r requirements.txt
```

### 3. Running the Web Application
Navigate to the `web-app/` directory to start the Laravel backend and Vite frontend server:

```bash
cd ../web-app

# Install PHP and NPM dependencies (if first time setup)
composer install
npm install

# Start Laravel development server
php artisan serve

# In a separate terminal, start Vite dev server
npm run dev
```

Open your browser and navigate to `http://127.0.0.1:8000` to access the application.

---

## Steganography Pipeline Workflow

1. **Text Encryption**: Secret text is encrypted using Fernet symmetric encryption (`encryption_key.pkl`).
2. **Bit Formatting**: Encrypted text bytes are converted into a binary bit array with header length metadata.
3. **Neural Encoding**: The `encoder.h5` Keras model embeds the binary bit payload into the cover image array.
4. **Neural Decoding**: The `decoder.h5` model extracts the raw bit array from the stego image, reads payload length header, and decrypts the text back to plain text.

---

## Contributors

- [**tee-uwu**](https://github.com/tee-uwu)
- [**Ayman Nasir (Ayman392)**](https://github.com/Ayman392)
- [**Ayubanas**](https://github.com/Ayubanas)
