#!/bin/bash

first="$1"
second="$2"

function remove-duplicates() {
    sort | uniq
}

function translit-utf8-to-ascii() {
    iconv --from-code=UTF-8 --to-code=ASCII//TRANSLIT
}

function remove-accents() {
    translit-utf8-to-ascii | remove-duplicates
}

function keep-unique-words() {
    local first="$1"
    local second="$2"

    comm -2 -3 "$first" "$second"
}

remove-accents < "$first" > "$first.translit"
remove-accents < "$second" > "$second.translit"

keep-unique-words "$first.translit" "$second.translit"
