all: \
	french-surnames.html \
	communes-departements-regions.csv \
	dela-fr-public-u8.dic.xml

french-surnames.html:
	curl "https://fr.wikipedia.org/wiki/Liste_de_pr%C3%A9noms_en_fran%C3%A7ais" > $@

# From:
# https://www.data.gouv.fr/fr/datasets/communes-de-france-base-des-codes-postaux/
communes-departements-regions.csv:
	curl "https://www.data.gouv.fr/fr/datasets/r/dbe8a621-a9c4-4bc3-9cae-be1699c5ff25" > $@

dela-fr-public-u8.dic.xml:
	curl http://infolingu.univ-mlv.fr/DonneesLinguistiques/Dictionnaires/dela-fr-public-u8-xml.zip > dela-fr-public-u8-xml.zip
	unzip dela-fr-public-u8-xml.zip dela-fr-public-u8.dic.xml
	rm dela-fr-public-u8-xml.zip
