---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![brodcast_wait_brick3](../../../media/bricks/brodcast_wait_brick3.png)

Manda un messaggio alle altre parti del tuo progetto e poi aspetta che finiscano il loro lavoro prima di andare avanti. È come chiedere a un amico di fare una cosa e aspettare che abbia finito prima di continuare.

**Perché inviare messaggi?**

Con i messaggi gli oggetti di una scena possono parlare tra loro e lavorare insieme. Un oggetto può mandare un messaggio a tutti gli altri oggetti. Gli oggetti che lo ricevono possono poi reagire a quel messaggio.

Questi messaggi **non si vedono** sullo schermo! Se vuoi mostrare dei messaggi, usa i blocchi della categoria “Aspetti”!

**Come funziona?**

- *Oggetto che invia:* l’oggetto che invia inizia la comunicazione con un blocco *“Invia a tutti”*. Questo blocco manda un messaggio a tutti gli altri oggetti della scena (anche allo sfondo).
- *Oggetto che riceve:* l’oggetto che riceve ascolta i messaggi e risponde. Usa il blocco *“Quando ricevi”* per capire quando arriva un certo messaggio.
- *Cosa succede col messaggio:* quando l’oggetto che riceve riceve il messaggio, esegue tutti i blocchi che stanno sotto il blocco *“Quando ricevi”*. Così puoi decidere tu cosa deve succedere quando arriva un certo messaggio.
