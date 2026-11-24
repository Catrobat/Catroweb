---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Pošalji poruku i čekaj](../../../media/bricks/brodcast_wait_brick3.png)

Šalje poruku drugim delovima tvog projekta i zatim čeka dok oni ne završe svoje zadatke, pa tek onda ide dalje. To je kao kada zamoliš prijatelja da nešto uradi i sačekaš da završi pre nego što nastaviš.

**Zašto slati poruke?**

Pomoću poruka možeš da razgovaraš i sarađuješ između različitih objekata u sceni. Jedan objekat može da pošalje poruku svim ostalim objektima, a objekti koji je dobiju mogu da reaguju na nju.

Ove poruke se **ne prikazuju** na ekranu! Ako želiš da prikažeš poruke, koristi blokove iz kategorije „Izgled“!

**Kako to radi?**

- *Objekat koji šalje:* Objekat koji šalje počinje komunikaciju blokom *„Pošalji poruku“*. Ovaj blok šalje poruku svim ostalim objektima u sceni (i pozadini).
- *Objekat koji prima:* Objekat koji prima sluša poruke i reaguje na njih. Koristi blok *„Kada primiš“* da bi prepoznao određenu poruku.
- *Obrada poruke:* Kada objekat dobije poruku, izvršiće sve blokove ispod bloka *„Kada primiš“*. Tako možeš da odrediš šta će se dogoditi kada stigne određena poruka.
