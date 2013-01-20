#!/usr/bin/env ruby

require "rubygems"
require "parallel"

pdf_file = ARGV[0]
output_dir = ARGV[1]

file = File.basename(ARGV[0], ".pdf")

total_page = `pdftk #{pdf_file} dump_data|grep NumberOfPages`
pages = total_page.split(" ")[1].to_i

# split to individual pdf file
output_file = "#{output_dir}#{file}-%d.pdf"
`pdftk #{pdf_file} burst output #{output_file}`
`chmod 777 #{output_dir}*.pdf`

# create high resolution `png` page
res = 138
arr = []
1.upto(pages) do |i| arr.push(i) end

Parallel.map(arr, :in_threads => 2) do |r|  
	input_file = "#{output_dir}#{file}-#{r}.pdf"
  	high_res_file = "#{output_dir}#{file}-#{r}-#{res}.jpg"
  #`convert -units PixelsPerInch -density 96x96 -quality 80 -resize #{res} -format jpg -quiet -background white -layers merge #{input_file} #{high_res_file}`
  `sudo ./bin/convert.sh 96x96 80 #{res} #{input_file} #{high_res_file}`
end
`chmod 777 #{output_dir}*.jpg`

# convert high resolution file into required thumbnails
#Parallel.map(['138','240','400','507','800','1034','1200'], :in_threads=>7) do |r|
#Parallel.map(['138'], :in_threads=>1) do |r|    
#  output_file = "#{output_dir}#{file}-%d-#{r}.jpg";
#  `convert -strip -define jpeg:size=128x128 -units PixelsPerInch -density 72x72 -quality 60 -resize #{r} -format jpg null: #{output_dir}*-#{res}.png #{output_file}`
#end
#`chmod 777 #{output_dir}*.jpg`

#remove junk image
#`rm #{output_dir}#{file}-0-*.jpg`

puts "Done";
