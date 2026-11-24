---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---
![Kada 2](../../../media/bricks/when-you-receive-__2.png)

Sa ovim blokom tvoj objekt sluša određenu prenosnu poruku i zatim pokreće skript kada je poruka primljena. Koristan je za komunikaciju između objekata i da im kažete kada da nešto urade.

![Pošalji poruku](../../../media/bricks/broadcast2.png)

**Zasto šaljete poruke?**

S porukama možeš komunicirati i interakciju između različitih objekata unutar scene. To omogućava jednom objektu da pošalje poruku svim drugim objektima, a primatelji objekata mogu tada da reagiraju ili odgovore na tu poruku.

Ove poruke su **ne prikazuje** na ekranu! Ako želiš da prikažete poruke, koristite blokove koda u kategoriji „Izgledi“!

**Kako to radi?**

- *Objekt pošiljatelja:* Objekt koji šalje pokreće komunikaciju pomoću bloka *„Pošalji poruku“*. Ovaj blok se koristi za šaljanje poruke svim ostalim predmetima u sceni (takođe i pozadini).
- *Objekt prijemnika:* Objekt koji prima je onaj koji sluša i odgovara na poruke. Koristi blok *„Kada primiš“* za otkrivanje kada se određena poruka prima.
- *Obrada poruka:* Kada primitelj objekta dobije poruku, on će izvršiti sve blokove koji su ispod blok *„Kada primiš“*. Ovo vam omogućava da definirate prilagođeno ponašanje ili akcije koje se dešavaju kada određena poruka bude primljena.
