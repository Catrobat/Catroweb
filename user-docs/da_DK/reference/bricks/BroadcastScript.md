---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Når du modtager](../../../media/bricks/when-you-receive-__2.png)

Med denne klods lytter dit objekt efter en bestemt besked og kører scriptet, når beskeden modtages. Det er nyttigt til at få objekter til at kommunikere og fortælle dem, hvornår de skal gøre noget.

![Send besked](../../../media/bricks/broadcast2.png)

**Hvorfor sende beskeder?**

Med beskeder kan du kommunikere og samarbejde mellem forskellige objekter i en scene. Et objekt kan sende en besked til alle andre objekter, og de objekter, der modtager beskeden, kan reagere på den.

Disse beskeder **vises ikke** på skærmen! Hvis du vil vise beskeder, så brug klodserne i kategorien “Udseende”!

**Hvordan virker det?**

- *Afsenderobjekt:* Afsenderobjektet starter kommunikationen ved at bruge en *“Send besked”*-klods. Klodsen sender en besked til alle andre objekter i scenen (også baggrunden).
- *Modtagerobjekt:* Modtagerobjektet er det, der lytter efter og reagerer på beskeder. Det bruger klodsen *“Når du modtager”* til at opdage, når en bestemt besked bliver modtaget.
- *Beskedhåndtering:* Når modtagerobjektet får beskeden, kører det alle klodser under *“Når du modtager”*. Det giver dig mulighed for at bestemme, hvad der skal ske, når en bestemt besked modtages.
