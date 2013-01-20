#!/bin/bash
#whoami
convert -units PixelsPerInch -density $1 -quality $2 -resize $3 -quiet -background white -layers merge $4 $5
#convert -units PixelsPerInch -density 72x72 -quality 60 -resize 138 -quiet -background white -layers merge $4 $5
#convert -units PixelsPerInch -density 72x72 -quality 60 -resize 138 -quiet -background white -layers merge /opt/lampp/htdocs/happy-pdf/trouble.pdf /opt/lampp/htdocs/happy-pdf/trouble.jpg
chmod 777 $5
