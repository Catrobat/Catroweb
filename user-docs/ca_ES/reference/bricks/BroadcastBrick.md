---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Envia el missatge](../../../media/bricks/broadcast2-1.png)

Envia un missatge a altres parts del teu projecte perquè comencin els seus propis scripts. És com fer un anunci que tothom escolta.

**Per què enviar missatges?**

Amb els missatges, els objectes d'una escena es poden comunicar i interactuar entre ells. Un objecte pot enviar un missatge a tots els altres objectes, i els que el reben poden reaccionar-hi.

Aquests missatges **no es mostren** a la pantalla! Si vols mostrar missatges, usa els blocs de la categoria «Aparença»!

**Com funciona?**

- *Objecte que envia:* L'objecte que envia comença la comunicació amb un bloc «Envia el missatge». Aquest bloc envia un missatge a tots els altres objectes de l'escena (també al fons).
- *Objecte que rep:* L'objecte que rep escolta els missatges i hi reacciona. Fa servir el bloc «Quan rebis» per saber quan rep un missatge concret.
- *Gestió del missatge:* Quan l'objecte que rep el missatge, executa tots els blocs que hi ha sota del bloc «Quan rebis». Així pots definir què passa quan arriba un missatge determinat.
