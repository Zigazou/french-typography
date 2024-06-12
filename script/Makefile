all: \
	corpus/french-words.txt \
	corpus/french-surnames.txt \
	corpus/french-cities.txt \
	frenchtypo.corrections.php

corpus/french-words.txt: data-source/dela-fr-public-u8.dic.xml
	php -d memory_limit=1G generate-french-words-txt.php $< > $@

corpus/french-surnames.txt: data-source/french-surnames.html
	php generate-french-surnames-txt.php $< > $@

corpus/french-cities.txt: data-source/communes-departements-regions.csv
	bash generate-french-cities-txt.bash $< > $@

frenchtypo.corrections.php: generate-frenchtypo-corrections-php.php corpus/french-words.txt corpus/french-surnames.txt corpus/french-cities.txt
	php generate-frenchtypo-corrections-php.php > $@

clean:
	rm -f corpus/french-words.txt corpus/french-surnames.txt corpus/french-cities.txt frenchtypo.corrections.php

install:
	cp frenchtypo.corrections.php ../src
