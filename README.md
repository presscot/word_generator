Generator słów/ciągów znaków.

Skrypt ma być wywoływany za pomocą CLI.
Każde słowo musi składać się z samych liter z alfabetu angielskiego.
W słowie mogą występować maksymalnie 2 spółgłoski z rzędu.
Każde słowo musi zaczynać się od spółgłoski, a drugą literą zawsze powinna być samogłoska.
Każde wywołanie skryptu powinno generować unikalne słowa.
Użytkownik ma mieć możliwość podania zakresu liczby znaków i ilości słów do wygenerowania.
Każde wykonanie skryptu powinno kończyć się wpisaniem do pliku "generator.log" następujących informacji:
- data wykonania skryptu,
- ilość wygenerowanych słów,
- minimalną i maksymalna ilość znaków. Jeżeli skrypt rzuca jakieś wyjątki, te również powinny znaleźć się osobnym pliku "exceptions.log"
  Skrypt powinien posiadać blokadę czasową, która uniemożliwi wywołanie go pomiędzy piątkiem od godz 15:00 a poniedziałkiem do 10:00, chyba że do wywołania komendy zostanie dodany parametr "--force".
  Każdy wygenerowany ciąg znaków powinien zostać zapisany do pliku words.txt


## How to start app:

```bash
$ ./install.sh # compile docker image and install vendors
$ ./bin/console.sh 'word:generate' 0 12 -min 3 -max 5 -count 30 --force #run app
$ ./clean.sh # to stop container and remove docker image
```

## Command prototype

./bin/console.sh 'word:generate' arg1 arg2 -min 3 -max 5 -count 30 --force

*options may be defined as "-opt var" or "--opt=var" and it is the same

| Name | TYPE | DEFAULT | DESCRIPTION |
| ------ | ------ | ------ | ------ |
| arg1 | string | aeiou | first group of chars (CONSONANTS) |
| arg2 | string | bcdfghjklmnpqrstvwxyz | second group of chars (VOWELS) |
| min | int | 5 | minimum number of characters |
| max | int | 5 | maximum number of characters |
| count | int | 10 | maximum number of results |
| force | bool | false | description above |
