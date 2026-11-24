---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Quan rebis](../../../media/bricks/when-you-receive-__2.png)

Amb aquest bloc, el teu objecte escolta un missatge concret i executa el script quan el rep. És útil per comunicar objectes entre ells i dir-los quan han de fer alguna cosa.

![Envia el missatge](../../../media/bricks/broadcast2.png)

**Per què enviar missatges?**

Amb els missatges, els objectes d'una escena es poden comunicar i interactuar entre ells. Un objecte pot enviar un missatge a tots els altres objectes, i els que el reben poden reaccionar-hi.

Aquests missatges **no es mostren** a la pantalla! Si vols mostrar missatges, usa els blocs de la categoria «Aparença»!

**Com funciona?**

- *Objecte que envia:* L'objecte que envia comença la comunicació amb un bloc «Envia el missatge». Aquest bloc envia un missatge a tots els altres objectes de l'escena (també al fons).
- *Objecte que rep:* L'objecte que rep escolta els missatges i hi reacciona. Fa servir el bloc «Quan rebis» per saber quan rep un missatge concret.
- *Gestió del missatge:* Quan l'objecte que rep el missatge, executa tots els blocs que hi ha sota del bloc «Quan rebis». Així pots definir què passa quan arriba un missatge determinat.
