---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---
![Pošalji poruku](../../../media/bricks/broadcast2-1.png)

Posla je poruku drugim delovima projekta, kazao im da počnu sopstveni scenarij. To je kao da objavljuješ nešto na šta svi slušaju.

**Zasto šaljete poruke?**

S porukama možeš komunicirati i interakciju između različitih objekata unutar scene. To omogućava jednom objektu da pošalje poruku svim drugim objektima, a primatelji objekata mogu tada da reagiraju ili odgovore na tu poruku.

Ove poruke su **ne prikazuje** na ekranu! Ako želiš da prikažete poruke, koristite blokove koda u kategoriji „Izgledi“!

**Kako to radi?**

- *Objekt pošiljatelja:* Objekt koji šalje pokreće komunikaciju pomoću bloka *„Pošalji poruku“*. Ovaj blok se koristi za šaljanje poruke svim ostalim predmetima u sceni (takođe i pozadini).
- *Objekt prijemnika:* Objekt koji prima je onaj koji sluša i odgovara na poruke. Koristi blok *„Kada primiš“* za otkrivanje kada se određena poruka prima.
- *Obrada poruka:* Kada primitelj objekta dobije poruku, on će izvršiti sve blokove koji su ispod blok *„Kada primiš“*. Ovo vam omogućava da definirate prilagođeno ponašanje ili akcije koje se dešavaju kada određena poruka bude primljena.
