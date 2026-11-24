---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![broadcast2](../../../media/bricks/broadcast2-1.png)

Manda un messaggio alle altre parti del tuo progetto e dice loro di far partire i loro script. È come fare un annuncio che tutti ascoltano.

**Perché inviare messaggi?**

Con i messaggi gli oggetti di una scena possono parlare tra loro e lavorare insieme. Un oggetto può mandare un messaggio a tutti gli altri oggetti. Gli oggetti che lo ricevono possono poi reagire a quel messaggio.

Questi messaggi **non si vedono** sullo schermo! Se vuoi mostrare dei messaggi, usa i blocchi della categoria “Aspetti”!

**Come funziona?**

- *Oggetto che invia:* l’oggetto che invia inizia la comunicazione con un blocco *“Invia a tutti”*. Questo blocco manda un messaggio a tutti gli altri oggetti della scena (anche allo sfondo).
- *Oggetto che riceve:* l’oggetto che riceve ascolta i messaggi e risponde. Usa il blocco *“Quando ricevi”* per capire quando arriva un certo messaggio.
- *Cosa succede col messaggio:* quando l’oggetto che riceve riceve il messaggio, esegue tutti i blocchi che stanno sotto il blocco *“Quando ricevi”*. Così puoi decidere tu cosa deve succedere quando arriva un certo messaggio.
