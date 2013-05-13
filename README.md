Happy-PDF-Reader
==============

Converts the PDF document into HTML5, do they can be read within browser.
Underneath it all, it usages imagemagick and pdftohtml to do all the conversion.


Demo:
http://174.34.171.67/happy-pdf/



To setup on server
- imagemagick
ImageMagick reads PDF files and convert them into image (png) format.
apt-get install imagemagick

- pdftk
Splits multi-page pdf into single pages.
sudo apt-get install pdftk

- pdftotext
It is used to extract text out of searchable pdf documents
apt-get install poppler-utils

- tesseract
Tesseract performs the actual ocr on your scanned images. More accurate than pdftohtml but bit slower.
apt-get install tesseract-ocr

- ruby
gem install parallel
Install ruby and then install gem parallel Some of the multi-threading PDF processing is done through Ruby and ruby gem Parallel.

Move /bin/nobody to /etc/sudoers.d directoy and set permission to 0440

Limitation:
- Password protected PDF are not converted.
- PDF with certain fonts are not converted.
- Not all PDF text are correctly converted.
