---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Kada primiš](../../../media/bricks/when-you-receive-__2.png)

Pomoću ovog bloka tvoj objekat sluša određenu poruku i pokreće skriptu kada poruka stigne. Korisno je za razgovor između objekata i za to da im kažeš kada treba nešto da urade.

![Pošalji poruku](../../../media/bricks/broadcast2.png)

**Zašto slati poruke?**

Pomoću poruka možeš da razgovaraš i sarađuješ između različitih objekata u sceni. Jedan objekat može da pošalje poruku svim ostalim objektima, a objekti koji je dobiju mogu da reaguju na nju.

Ove poruke se **ne prikazuju** na ekranu! Ako želiš da prikažeš poruke, koristi blokove iz kategorije „Izgled“!

**Kako to radi?**

- *Objekat koji šalje:* Objekat koji šalje počinje komunikaciju blokom *„Pošalji poruku“*. Ovaj blok šalje poruku svim ostalim objektima u sceni (i pozadini).
- *Objekat koji prima:* Objekat koji prima sluša poruke i reaguje na njih. Koristi blok *„Kada primiš“* da bi prepoznao određenu poruku.
- *Obrada poruke:* Kada objekat dobije poruku, izvršiće sve blokove ispod bloka *„Kada primiš“*. Tako možeš da odrediš šta će se dogoditi kada stigne određena poruka.
