# Thông tin về cách hoạt động của luồng MVC

### 1. File index.php
Chịu trách nhiệm điều hướng khi user gửi yêu cầu truy cập màn hình, tính năng nào đó

### 2. flie favicon.ico 
Là file logo của dự án 

### 3. models/ pdo.php
Chịu trách nghiệm định nghĩa đối tượng, tương tác với database

### 4. folder views
Chịu trách nghiệm xử lý logic và hiển thị file view tương ứng


### 5. folder asset
Phần liên quan đến giao diện người dùng

trong đó( File index.php xử lý router của cả trang web.
Folder views chứa giao diện liên quan đến phần khách hàng.
Folder admin chứa giao diện liên quan đến quản trị.
Folder upload chứa ảnh được upload từ người quản trị
Folder assets chứa các css, js, image,
Folder module chứa các function tương tác với database)
