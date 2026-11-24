---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Khi bạn nhận được](../../../media/bricks/when-you-receive-__2.png)

Với khối này, đối tượng của bạn lắng nghe một thông điệp phát tin cụ thể, rồi chạy kịch bản khi nhận được thông điệp đó. Hữu ích để các đối tượng trao đổi với nhau và biết lúc nào cần làm gì.

![Phát tin](../../../media/bricks/broadcast2.png)

**Tại sao lại gửi thông điệp phát tin?**

Nhờ thông điệp, các đối tượng trong một cảnh có thể trao đổi và tương tác với nhau. Một đối tượng có thể gửi thông điệp tới tất cả các đối tượng khác, và các đối tượng nhận có thể phản ứng lại thông điệp đó.

Những thông điệp này **không hiển thị** trên màn hình! Nếu bạn muốn hiển thị chữ, hãy dùng các khối lệnh trong nhóm “Diện mạo”!

**Hoạt động như thế nào?**

- *Đối tượng gửi:* Đối tượng gửi bắt đầu giao tiếp bằng khối “Phát tin”. Khối này gửi thông điệp tới tất cả các đối tượng khác trong cảnh (kể cả phông nền).
- *Đối tượng nhận:* Đối tượng nhận lắng nghe và phản ứng với thông điệp. Nó dùng khối “Khi bạn nhận được” để biết khi nào có một thông điệp cụ thể.
- *Xử lý thông điệp:* Khi đối tượng nhận được thông điệp, nó sẽ chạy tất cả các khối nằm bên dưới khối “Khi bạn nhận được”. Nhờ vậy, bạn có thể tự đặt hành động khi nhận một thông điệp nào đó.
