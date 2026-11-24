---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Phát tin và đợi](../../../media/bricks/brodcast_wait_brick3.png)

Gửi một thông điệp tới các phần khác của dự án, rồi đợi đến khi chúng làm xong việc mới đi tiếp. Giống như bạn nhờ một người bạn làm việc gì đó và đợi bạn ấy xong rồi mới làm tiếp.

**Tại sao lại gửi thông điệp phát tin?**

Nhờ thông điệp, các đối tượng trong một cảnh có thể trao đổi và tương tác với nhau. Một đối tượng có thể gửi thông điệp tới tất cả các đối tượng khác, và các đối tượng nhận có thể phản ứng lại thông điệp đó.

Những thông điệp này **không hiển thị** trên màn hình! Nếu bạn muốn hiển thị chữ, hãy dùng các khối lệnh trong nhóm “Diện mạo”!

**Hoạt động như thế nào?**

- *Đối tượng gửi:* Đối tượng gửi bắt đầu giao tiếp bằng khối “Phát tin”. Khối này gửi thông điệp tới tất cả các đối tượng khác trong cảnh (kể cả phông nền).
- *Đối tượng nhận:* Đối tượng nhận lắng nghe và phản ứng với thông điệp. Nó dùng khối “Khi bạn nhận được” để biết khi nào có một thông điệp cụ thể.
- *Xử lý thông điệp:* Khi đối tượng nhận được thông điệp, nó sẽ chạy tất cả các khối nằm bên dưới khối “Khi bạn nhận được”. Nhờ vậy, bạn có thể tự đặt hành động khi nhận một thông điệp nào đó.
