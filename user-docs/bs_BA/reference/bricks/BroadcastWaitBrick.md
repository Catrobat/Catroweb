---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---
![Pošalji poruku i čekaj](../../../media/bricks/brodcast_wait_brick3.png)

Poslaće poruku drugim delovima projekta i onda čeka dok ne završe svoje zadatke pre nego što nastave. To je kao da tražiš od prijatelja da uradi nešto i čekaš da završi pre nego što nastaviš.

**Zasto šaljete poruke?**

S porukama možeš komunicirati i interakciju između različitih objekata unutar scene. To omogućava jednom objektu da pošalje poruku svim drugim objektima, a primatelji objekata mogu tada da reagiraju ili odgovore na tu poruku.

Ove poruke su **ne prikazuje** na ekranu! Ako želiš da prikažete poruke, koristite blokove koda u kategoriji „Izgledi“!

**Kako to radi?**

- *Objekt pošiljatelja:* Objekt koji šalje pokreće komunikaciju pomoću bloka *„Pošalji poruku“*. Ovaj blok se koristi za šaljanje poruke svim ostalim predmetima u sceni (takođe i pozadini).
- *Objekt prijemnika:* Objekt koji prima je onaj koji sluša i odgovara na poruke. Koristi blok *„Kada primiš“* za otkrivanje kada se određena poruka prima.
- *Obrada poruka:* Kada primitelj objekta dobije poruku, on će izvršiti sve blokove koji su ispod blok *„Kada primiš“*. Ovo vam omogućava da definirate prilagođeno ponašanje ili akcije koje se dešavaju kada određena poruka bude primljena.
