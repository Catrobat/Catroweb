---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Pošalji poruku](../../../media/bricks/broadcast2-1.png)

Šalje poruku drugim dijelovima tvog projekta i govori im da pokrenu svoje skripte. Kao da objaviš obavijest koju svi slušaju.

**Zašto slati poruke?**

Pomoću poruka možeš komunicirati i djelovati zajedno među različitim objektima na sceni. Jedan objekt može poslati poruku svim ostalim objektima, a objekti koji je primaju mogu na nju reagirati.

Ove poruke **nisu prikazane** na zaslonu! Ako želiš prikazati poruke, koristi blokove iz kategorije „Izgledi”!

**Kako to funkcionira?**

- *Objekt pošiljatelj:* Objekt pošiljatelj pokreće komunikaciju pomoću bloka *„Pošalji poruku”*. Taj blok koristi se za slanje poruke svim ostalim objektima na sceni (također i pozadini).
- *Objekt primatelj:* Objekt primatelj je onaj koji osluškuje poruke i reagira na njih. Koristi blok *„Kad primiš”* da otkrije kad se prima određena poruka.
- *Obrada poruke:* Kad objekt primatelj primi poruku, izvršit će sve blokove ispod bloka *„Kad primiš”*. To ti omogućuje da odrediš vlastito ponašanje ili radnje koje se događaju kad se primi određena poruka.
