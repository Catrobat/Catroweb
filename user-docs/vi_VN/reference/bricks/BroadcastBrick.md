---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Phát tin](../../../media/bricks/broadcast2-1.png)

Gửi một thông điệp tới các phần khác của dự án, bảo chúng bắt đầu kịch bản của riêng mình. Giống như bạn thông báo để mọi người cùng nghe.

**Tại sao lại gửi thông điệp phát tin?**

Nhờ thông điệp, các đối tượng trong một cảnh có thể trao đổi và tương tác với nhau. Một đối tượng có thể gửi thông điệp tới tất cả các đối tượng khác, và các đối tượng nhận có thể phản ứng lại thông điệp đó.

Những thông điệp này **không hiển thị** trên màn hình! Nếu bạn muốn hiển thị chữ, hãy dùng các khối lệnh trong nhóm “Diện mạo”!

**Hoạt động như thế nào?**

- *Đối tượng gửi:* Đối tượng gửi bắt đầu giao tiếp bằng khối “Phát tin”. Khối này gửi thông điệp tới tất cả các đối tượng khác trong cảnh (kể cả phông nền).
- *Đối tượng nhận:* Đối tượng nhận lắng nghe và phản ứng với thông điệp. Nó dùng khối “Khi bạn nhận được” để biết khi nào có một thông điệp cụ thể.
- *Xử lý thông điệp:* Khi đối tượng nhận được thông điệp, nó sẽ chạy tất cả các khối nằm bên dưới khối “Khi bạn nhận được”. Nhờ vậy, bạn có thể tự đặt hành động khi nhận một thông điệp nào đó.
