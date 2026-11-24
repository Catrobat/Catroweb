---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![broadcast2](../../../media/bricks/broadcast2-1.png)

Wysyła wiadomość do innych części twojego projektu, mówiąc im, żeby uruchomiły swoje skrypty. To jak ogłoszenie, którego wszyscy słuchają.

**Po co wysyłać wiadomości?**

Dzięki wiadomościom różne obiekty w scenie mogą się ze sobą komunikować i na siebie reagować. Jeden obiekt może wysłać wiadomość do wszystkich innych obiektów, a obiekty, które ją odbiorą, mogą na nią zareagować.

Te wiadomości **nie są wyświetlane** na ekranie! Jeśli chcesz coś wyświetlić, użyj klocków z kategorii „Wygląd”!

**Jak to działa?**

- *Obiekt nadawca:* Obiekt nadawca rozpoczyna komunikację, używając klocka *„Nadaj”*. Ten klocek wysyła wiadomość do wszystkich innych obiektów w scenie (także do tła).
- *Obiekt odbiorca:* Obiekt odbiorca nasłuchuje wiadomości i na nie reaguje. Używa klocka *„Kiedy otrzymasz”*, żeby wykryć, kiedy przychodzi konkretna wiadomość.
- *Obsługa wiadomości:* Kiedy obiekt odbiorca dostanie wiadomość, wykona wszystkie klocki pod klockiem *„Kiedy otrzymasz”*. Dzięki temu możesz określić, co ma się stać, gdy przyjdzie dana wiadomość.
