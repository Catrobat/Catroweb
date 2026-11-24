---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Pošalji poruku i čekaj](../../../media/bricks/brodcast_wait_brick3.png)

Šalje poruku drugim dijelovima tvog projekta i zatim čeka dok ne završe svoje zadatke prije nego što nastavi. Kao da zamoliš prijatelja da nešto učini i čekaš da završi prije nego što nastaviš.

**Zašto slati poruke?**

Pomoću poruka možeš komunicirati i djelovati zajedno među različitim objektima na sceni. Jedan objekt može poslati poruku svim ostalim objektima, a objekti koji je primaju mogu na nju reagirati.

Ove poruke **nisu prikazane** na zaslonu! Ako želiš prikazati poruke, koristi blokove iz kategorije „Izgledi”!

**Kako to funkcionira?**

- *Objekt pošiljatelj:* Objekt pošiljatelj pokreće komunikaciju pomoću bloka *„Pošalji poruku”*. Taj blok koristi se za slanje poruke svim ostalim objektima na sceni (također i pozadini).
- *Objekt primatelj:* Objekt primatelj je onaj koji osluškuje poruke i reagira na njih. Koristi blok *„Kad primiš”* da otkrije kad se prima određena poruka.
- *Obrada poruke:* Kad objekt primatelj primi poruku, izvršit će sve blokove ispod bloka *„Kad primiš”*. To ti omogućuje da odrediš vlastito ponašanje ili radnje koje se događaju kad se primi određena poruka.
