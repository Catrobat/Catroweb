---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Envia el missatge i espera](../../../media/bricks/brodcast_wait_brick3.png)

Envia un missatge a altres parts del teu projecte i després espera que acabin les seves tasques abans de continuar. És com demanar a un amic que faci una cosa i esperar que hi acabi abans de seguir.

**Per què enviar missatges?**

Amb els missatges, els objectes d'una escena es poden comunicar i interactuar entre ells. Un objecte pot enviar un missatge a tots els altres objectes, i els que el reben poden reaccionar-hi.

Aquests missatges **no es mostren** a la pantalla! Si vols mostrar missatges, usa els blocs de la categoria «Aparença»!

**Com funciona?**

- *Objecte que envia:* L'objecte que envia comença la comunicació amb un bloc «Envia el missatge». Aquest bloc envia un missatge a tots els altres objectes de l'escena (també al fons).
- *Objecte que rep:* L'objecte que rep escolta els missatges i hi reacciona. Fa servir el bloc «Quan rebis» per saber quan rep un missatge concret.
- *Gestió del missatge:* Quan l'objecte que rep el missatge, executa tots els blocs que hi ha sota del bloc «Quan rebis». Així pots definir què passa quan arriba un missatge determinat.
