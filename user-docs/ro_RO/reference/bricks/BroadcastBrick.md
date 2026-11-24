---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Transmite](../../../media/bricks/broadcast2-1.png)

Trimite un mesaj către alte părți ale proiectului tău, spunându-le să-și pornească propriile scripturi. Este ca și cum ai face un anunț pe care îl aud toți.

**De ce să trimiți mesaje de transmisie?**

Cu ajutorul mesajelor, obiectele dintr-o scenă pot comunica și pot interacționa între ele. Un obiect poate trimite un mesaj tuturor celorlalte obiecte, iar obiectele care primesc mesajul pot reacționa la el.

Aceste mesaje **nu se afișează** pe ecran! Dacă vrei să afișezi mesaje, folosește blocurile din categoria „Aspect”!

**Cum funcționează?**

- *Obiectul care trimite:* Obiectul care trimite începe comunicarea folosind un bloc „Transmite”. Acest bloc trimite un mesaj tuturor celorlalte obiecte din scenă (și fundalului).
- *Obiectul care primește:* Obiectul care primește este cel care ascultă și reacționează la mesaje. Folosește blocul „Când primești” ca să știe când a sosit un mesaj anume.
- *Gestionarea mesajului:* Când obiectul care primește mesajul îl primește, va executa toate blocurile de sub blocul „Când primești”. Așa poți stabili ce comportament sau ce acțiuni au loc când se primește un anumit mesaj.
