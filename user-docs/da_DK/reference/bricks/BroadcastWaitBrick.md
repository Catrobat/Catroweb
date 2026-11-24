---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Send besked og vent](../../../media/bricks/brodcast_wait_brick3.png)

Sender en besked til andre dele af dit projekt og venter derefter, til de er færdige med deres opgaver, før den går videre. Det er som at bede en ven om at gøre noget og vente, til de er færdige, før du fortsætter.

**Hvorfor sende beskeder?**

Med beskeder kan du kommunikere og samarbejde mellem forskellige objekter i en scene. Et objekt kan sende en besked til alle andre objekter, og de objekter, der modtager beskeden, kan reagere på den.

Disse beskeder **vises ikke** på skærmen! Hvis du vil vise beskeder, så brug klodserne i kategorien “Udseende”!

**Hvordan virker det?**

- *Afsenderobjekt:* Afsenderobjektet starter kommunikationen ved at bruge en *“Send besked”*-klods. Klodsen sender en besked til alle andre objekter i scenen (også baggrunden).
- *Modtagerobjekt:* Modtagerobjektet er det, der lytter efter og reagerer på beskeder. Det bruger klodsen *“Når du modtager”* til at opdage, når en bestemt besked bliver modtaget.
- *Beskedhåndtering:* Når modtagerobjektet får beskeden, kører det alle klodser under *“Når du modtager”*. Det giver dig mulighed for at bestemme, hvad der skal ske, når en bestemt besked modtages.
