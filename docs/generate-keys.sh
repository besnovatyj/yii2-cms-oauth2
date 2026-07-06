#!/bin/bash

#
# Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
#

# Generate OAuth2 private and public keys for JWT tokens

# Default output directory
OUTPUT_DIR="${1:-./keys}"

# Create directory if it doesn't exist
mkdir -p "$OUTPUT_DIR"

echo "Generating OAuth2 keys in $OUTPUT_DIR..."

# Generate private key (2048 bit RSA)
openssl genrsa -out "$OUTPUT_DIR/private.key" 2048

# Generate public key from private key
openssl rsa -in "$OUTPUT_DIR/private.key" -pubout -out "$OUTPUT_DIR/public.key"

# Set permissions
chmod 600 "$OUTPUT_DIR/private.key"
chmod 644 "$OUTPUT_DIR/public.key"

echo "Keys generated successfully!"
echo "Private key: $OUTPUT_DIR/private.key"
echo "Public key: $OUTPUT_DIR/public.key"
echo ""
echo "IMPORTANT: Keep the private key secure and never commit it to version control!"
echo "Add $OUTPUT_DIR/ to your .gitignore file."
