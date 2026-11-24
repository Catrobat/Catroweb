---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Pošalji poruku](../../../media/bricks/broadcast2-1.png)

Šalje poruku drugim delovima tvog projekta i kaže im da pokrenu svoje skripte. To je kao kada objaviš nešto i svi to čuju.

**Zašto slati poruke?**

Pomoću poruka možeš da razgovaraš i sarađuješ između različitih objekata u sceni. Jedan objekat može da pošalje poruku svim ostalim objektima, a objekti koji je dobiju mogu da reaguju na nju.

Ove poruke se **ne prikazuju** na ekranu! Ako želiš da prikažeš poruke, koristi blokove iz kategorije „Izgled“!

**Kako to radi?**

- *Objekat koji šalje:* Objekat koji šalje počinje komunikaciju blokom *„Pošalji poruku“*. Ovaj blok šalje poruku svim ostalim objektima u sceni (i pozadini).
- *Objekat koji prima:* Objekat koji prima sluša poruke i reaguje na njih. Koristi blok *„Kada primiš“* da bi prepoznao određenu poruku.
- *Obrada poruke:* Kada objekat dobije poruku, izvršiće sve blokove ispod bloka *„Kada primiš“*. Tako možeš da odrediš šta će se dogoditi kada stigne određena poruka.
