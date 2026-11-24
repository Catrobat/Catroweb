---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Când primești](../../../media/bricks/when-you-receive-__2.png)

Cu acest bloc, obiectul tău ascultă un anumit mesaj de transmisie și apoi rulează scriptul când primește mesajul. Este util pentru a comunica între obiecte și a le spune când să facă ceva.

![Transmite](../../../media/bricks/broadcast2.png)

**De ce să trimiți mesaje de transmisie?**

Cu ajutorul mesajelor, obiectele dintr-o scenă pot comunica și pot interacționa între ele. Un obiect poate trimite un mesaj tuturor celorlalte obiecte, iar obiectele care primesc mesajul pot reacționa la el.

Aceste mesaje **nu se afișează** pe ecran! Dacă vrei să afișezi mesaje, folosește blocurile din categoria „Aspect”!

**Cum funcționează?**

- *Obiectul care trimite:* Obiectul care trimite începe comunicarea folosind un bloc „Transmite”. Acest bloc trimite un mesaj tuturor celorlalte obiecte din scenă (și fundalului).
- *Obiectul care primește:* Obiectul care primește este cel care ascultă și reacționează la mesaje. Folosește blocul „Când primești” ca să știe când a sosit un mesaj anume.
- *Gestionarea mesajului:* Când obiectul care primește mesajul îl primește, va executa toate blocurile de sub blocul „Când primești”. Așa poți stabili ce comportament sau ce acțiuni au loc când se primește un anumit mesaj.
