#!/bin/bash

function get-only-city-name() {
    cut --delimiter=, --fields=10
}

function remove-duplicates() {
    sort | uniq
}

function ignore-empty-result() {
    cat
}

cat "$1" \
    | get-only-city-name \
    | remove-duplicates \
    | ignore-empty-result
