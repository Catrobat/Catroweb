---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Kad primiš poruku](../../../media/bricks/when-you-receive-__2.png)

Ovim blokom tvoj objekt osluškuje određenu poruku i pokreće skriptu kad je primi. Korisno za komunikaciju među objektima i za to da im kažeš kad trebaju nešto raditi.

![Pošalji poruku](../../../media/bricks/broadcast2.png)

**Zašto slati poruke?**

Pomoću poruka možeš komunicirati i djelovati zajedno među različitim objektima na sceni. Jedan objekt može poslati poruku svim ostalim objektima, a objekti koji je primaju mogu na nju reagirati.

Ove poruke **nisu prikazane** na zaslonu! Ako želiš prikazati poruke, koristi blokove iz kategorije „Izgledi”!

**Kako to funkcionira?**

- *Objekt pošiljatelj:* Objekt pošiljatelj pokreće komunikaciju pomoću bloka *„Pošalji poruku”*. Taj blok koristi se za slanje poruke svim ostalim objektima na sceni (također i pozadini).
- *Objekt primatelj:* Objekt primatelj je onaj koji osluškuje poruke i reagira na njih. Koristi blok *„Kad primiš”* da otkrije kad se prima određena poruka.
- *Obrada poruke:* Kad objekt primatelj primi poruku, izvršit će sve blokove ispod bloka *„Kad primiš”*. To ti omogućuje da odrediš vlastito ponašanje ili radnje koje se događaju kad se primi određena poruka.
