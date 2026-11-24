---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![when-you-receive-__2](../../../media/bricks/when-you-receive-__2.png)

Con questo blocco il tuo oggetto ascolta un certo messaggio. Quando il messaggio arriva, lo script parte. È utile per far parlare gli oggetti tra loro e dire loro quando fare qualcosa.

![broadcast2](../../../media/bricks/broadcast2.png)

**Perché inviare messaggi?**

Con i messaggi gli oggetti di una scena possono parlare tra loro e lavorare insieme. Un oggetto può mandare un messaggio a tutti gli altri oggetti. Gli oggetti che lo ricevono possono poi reagire a quel messaggio.

Questi messaggi **non si vedono** sullo schermo! Se vuoi mostrare dei messaggi, usa i blocchi della categoria “Aspetti”!

**Come funziona?**

- *Oggetto che invia:* l’oggetto che invia inizia la comunicazione con un blocco *“Invia a tutti”*. Questo blocco manda un messaggio a tutti gli altri oggetti della scena (anche allo sfondo).
- *Oggetto che riceve:* l’oggetto che riceve ascolta i messaggi e risponde. Usa il blocco *“Quando ricevi”* per capire quando arriva un certo messaggio.
- *Cosa succede col messaggio:* quando l’oggetto che riceve riceve il messaggio, esegue tutti i blocchi che stanno sotto il blocco *“Quando ricevi”*. Così puoi decidere tu cosa deve succedere quando arriva un certo messaggio.
