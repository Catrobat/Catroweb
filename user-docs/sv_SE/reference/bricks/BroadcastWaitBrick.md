---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Skicka och vänta](../../../media/bricks/brodcast_wait_brick3.png)

Skickar ett meddelande till andra delar av ditt projekt och väntar sedan tills de är klara innan du går vidare. Det är som att be en kompis göra något och vänta tills hen är klar innan du fortsätter.

**Varför skicka meddelanden?**

Med meddelanden kan olika objekt i en scen prata med varandra och påverka varandra. Ett objekt kan skicka ett meddelande till alla andra objekt, och de som tar emot det kan reagera på meddelandet.

Meddelandena **visas inte** på skärmen! Om du vill visa text använder du blocken i kategorin ”Utseende”!

**Så här fungerar det**

- *Avsändaren:* Avsändaren startar kommunikationen med ett *”Skicka”*-block. Blocket skickar ett meddelande till alla andra objekt i scenen (även bakgrunden).
- *Mottagaren:* Mottagaren är objektet som lyssnar och reagerar på meddelanden. Det använder blocket *”När du tar emot”* för att märka när ett visst meddelande kommer.
- *Hantering av meddelandet:* När mottagaren får meddelandet kör den alla block som ligger under *”När du tar emot”*. Så kan du bestämma vad som ska hända när ett visst meddelande kommer.
