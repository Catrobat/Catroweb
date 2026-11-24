---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![when-you-receive-__2](../../../media/bricks/when-you-receive-__2.png)

Dzięki temu klockowi twój obiekt nasłuchuje konkretnej wiadomości i uruchamia skrypt, kiedy ją otrzyma. Przydaje się do komunikacji między obiektami i do mówienia im, kiedy mają coś zrobić.

![broadcast2](../../../media/bricks/broadcast2.png)

**Po co wysyłać wiadomości?**

Dzięki wiadomościom różne obiekty w scenie mogą się ze sobą komunikować i na siebie reagować. Jeden obiekt może wysłać wiadomość do wszystkich innych obiektów, a obiekty, które ją odbiorą, mogą na nią zareagować.

Te wiadomości **nie są wyświetlane** na ekranie! Jeśli chcesz coś wyświetlić, użyj klocków z kategorii „Wygląd”!

**Jak to działa?**

- *Obiekt nadawca:* Obiekt nadawca rozpoczyna komunikację, używając klocka *„Nadaj”*. Ten klocek wysyła wiadomość do wszystkich innych obiektów w scenie (także do tła).
- *Obiekt odbiorca:* Obiekt odbiorca nasłuchuje wiadomości i na nie reaguje. Używa klocka *„Kiedy otrzymasz”*, żeby wykryć, kiedy przychodzi konkretna wiadomość.
- *Obsługa wiadomości:* Kiedy obiekt odbiorca dostanie wiadomość, wykona wszystkie klocki pod klockiem *„Kiedy otrzymasz”*. Dzięki temu możesz określić, co ma się stać, gdy przyjdzie dana wiadomość.
