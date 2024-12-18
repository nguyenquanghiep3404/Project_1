<?php 
require_once './models/Product.php';
require_once './models/ProductQuery.php';

ob_start();

class ProductAdminController {

    public $productQuery;
    
    // Khai báo phương thức 
    public function __construct()
    {
        // 1. Khởi tạo giá trị cho thuộc tính productQuery
        $this->productQuery = new ProductQuery();
        // Mở trình duyệt lên để kiểm tra kết quả

    }

    public function showAdmin(){
        if (!isset($_SESSION['name'])) {
            header('location:?action=login'); // Chuyển hướng đến trang đăng nhập
            exit();
        }
        // Kiểm tra quyền của người dùng phải có role =1 thì mới được vào admin
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 0) {
            header('location:?action=403'); // Chuyển hướng đến trang lỗi không đủ quyền
            exit();
        }
        $totalProducts = $this->productQuery->getTotalProducts();
        $totalUser = $this->productQuery->getTotalUser();
        $totalCart = $this->productQuery->getTotalCart();
        $totalComment = $this->productQuery->getTotalComment();
        $totalCategories = $this->productQuery->getTotalCategories();


        require_once './views/admin/dashboard.php';
    }
    public function showProductsByCategory($category_id) {
        // Lấy danh mục và sản phẩm theo danh mục
        $products = $this->productQuery->getProductsByCategory($category_id);
        $categories = $this->productQuery->getAllCategories();
        
        // Truyền dữ liệu cho view
        require_once('./views/client/categoryProductClient.php');
    }

    // Hiện sản phẩm
    public function showList()
    {   
        // // Kiểm tra quyền của người dùng phải có role = 0 thì mới được vào admin
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 0) {
            header('location:?action=403'); // Chuyển hướng đến trang lỗi không đủ quyền
            exit();
        }
        // logic quyền admin
        if (!isset($_SESSION['name'])) {
            header('location:?action=login'); // Chuyển hướng đến trang đăng nhập
            exit();
        }
        
        // 1. Gọi xuống model để lấy danh sách product
        $danhSachProduct = $this->productQuery->getAllProduct();
        $variant = $this->productQuery->get_allvariant();
        $listCategories = $this->productQuery->getAllCategories();
        $product = $this->productQuery->render_allproduct();
        
        // Hiển thị file view
        include './views/admin/product/list.php'; // Gọi đến view danh sách sản phẩm
    }
    // Thêm mới sản phẩm vào database
    public function Create()
    {   
        // logic quyền admin
        if (!isset($_SESSION['name'])) {
            header('location:?action=login'); // Chuyển hướng đến trang đăng nhập
            exit();
        }
        // Kiểm tra quyền của người dùng phải có role =0 thì mới được vào admin
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 0) {
            header('location:?action=403'); // Chuyển hướng đến trang lỗi không đủ quyền
            exit();
        }
        if((isset($_POST['themmoi'])) && ($_POST['themmoi'])) {
            
                $name = $_POST['product_name'];
                $category_id = $_POST['category_id'];
                $price = $_POST['product_price'];
                $sale_price = isset($_POST['product_sale_price']) && !empty($_POST['product_sale_price']) ? $_POST['product_sale_price'] : null;
                $description = $_POST['product_description'];

        // Xử lý tải lên ảnh chính
        $image = "";
            // luu hinh ảnh vao
        if($_FILES["product_image"]["error"] == UPLOAD_ERR_OK) {
            $target_dir = "./uploads/product/";
            $target_file = $target_dir.basename($_FILES["product_image"]["name"]);

            if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image = $target_file;
            } else {
                $message = "Lỗi khi tải lên ảnh.";
            }
        }
        
        // Xử lý việc tải nhiều ảnh
        $gallery_images = [];
        $target_dir_gallery = "./uploads/product_gallery/";

        if(isset($_FILES["product_gallery"])) {
            foreach($_FILES["product_gallery"]["tmp_name"] as $key => $tmp_name) {
                $gallery_image_name = $_FILES["product_gallery"]["name"][$key];
                $gallery_target_file = $target_dir_gallery.basename($gallery_image_name);

                // Chỉ xử lý ảnh nếu người dùng đã tải lên
                if($_FILES["product_gallery"]["error"][$key] == UPLOAD_ERR_OK) {
                    if(move_uploaded_file($tmp_name, $gallery_target_file)) {
                        $gallery_images[] = $gallery_target_file;
                    } else {
                        $message = "Lỗi khi tải lên ảnh trong gallery.";
                        break;
                    }
                }
            }
        }
         
        if (empty($message)) {
            // Chuyển gallery thành JSON
            $gallery = json_encode($gallery_images);

            // Thêm sản phẩm vào bảng `products`
            $product_id = $this->productQuery->addProduct($name, $image, $price, $category_id, $sale_price, $description, $gallery);

            // Lưu biến thể sản phẩm vào bảng `product_variants`
            foreach ($_POST['variant_size'] as $key => $size) {
                $color = $_POST['variant_color'][$key];
                $quantity = $_POST['variant_quantity'][$key];

                $this->productQuery->addProductVariants($product_id, $size, $color, $quantity);
            }

            header('Location: index.php?action=product');
        }
        }// END if submit form
        
        // lấy danh mục
        $listCategories = $this->productQuery->getAllCategories();
        
        include "./views/admin/product/create.php";
    }// END Create()
    public function Edit()
    {   
        // logic quyền admin
        if (!isset($_SESSION['name'])) {
            header('location:?action=login'); // Chuyển hướng đến trang đăng nhập
            exit();
        }
        // Kiểm tra quyền của người dùng phải có role =1 thì mới được vào admin
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 0) {
            header('location:?action=403'); // Chuyển hướng đến trang lỗi không đủ quyền
            exit();
        }
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $one =$this->productQuery->getone_product($id);
        }
        if (isset($_GET['delete_variant'])) {
            $variant_id = $_GET['delete_variant'];
                     
            $this->productQuery->deleteProductVariant($variant_id);
            
            header("Location: ?action=product-form-edit&id=".$id);
            exit();
        }
            // if ($one) {
            //     // Sản phẩm được tìm thấy
            //     // Thực hiện xử lý tiếp
            //     print_r($one);die; // In thông tin sản phẩm (test)
            // } else {
            //     // Không tìm thấy sản phẩm
            //     echo "Không tìm thấy sản phẩm với ID: $id";
            // }

        // } else {
        //     // Tham số ID không được truyền qua URL
        //     echo "Tham số ID không tồn tại trong URL.";
        // }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capnhat'])) {
            $category_id = $_POST['category_id'] ?? null;
            $name = $_POST['name'] ?? null;
            $description = $_POST['product_description'] ??null;
            $price = $_POST['product_price'] ??null;
            $sale_price = $_POST['product_sale_price'] ??null;
            $size = $_POST['size'] ?? null;
            $color = $_POST['color'] ??null;
            $quantity = $_POST['quantity'] ??null;

            // xu li tai len anh chinh
            $image = "";

                // luu hinh ảnh vao
            if($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
                $target_dir = "./uploads/product/";
                $target_file = $target_dir.basename($_FILES["image"]["name"]);

                if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                } else {
                    $message = "Lỗi khi tải lên ảnh.";
                }
            }
            // Xử lý việc tải nhiều ảnh
            $gallery_images = [];
            $target_dir_gallery = "./uploads/product_gallery/";

            if(isset($_FILES["product_gallery"])) {
            foreach($_FILES["product_gallery"]["tmp_name"] as $key => $tmp_name) {
                $gallery_image_name = $_FILES["product_gallery"]["name"][$key];
                $gallery_target_file = $target_dir_gallery.basename($gallery_image_name);

                // Chỉ xử lý ảnh nếu người dùng đã tải lên
                if($_FILES["product_gallery"]["error"][$key] == UPLOAD_ERR_OK) {
                    if(move_uploaded_file($tmp_name, $gallery_target_file)) {
                        $gallery_images[] = $gallery_target_file;
                    } else {
                        $message = "Lỗi khi tải lên ảnh trong gallery.";
                        break;
                    }
                }
            }
            }
            if(empty($message)) {
                $galleryData = ["images" => $gallery_images];
                $gallery = json_encode($gallery_images);

                if($image != "" && $gallery_images != "") {
                    $this->productQuery->update_product($name, $image,	$price,$category_id,$sale_price, $description,$gallery, $id);
                } else {
                    $this->productQuery->update_product_noneimg($name,	$price,$category_id,$sale_price, $description,$id );
                }
            }
            // Cập nhật biến thể
            foreach ($_POST['variant_id'] as $key => $variant_id) {
                            $size = $_POST['variant_size'][$key];
                            $color = $_POST['variant_color'][$key];
                            $quantity = $_POST['variant_quantity'][$key];
                            
                            $this->productQuery->updateProductVariant($variant_id, $size, $color, $quantity);}
             // Thêm các biến thể mới nếu có
             if (isset($_POST['new_variant_size'])) {
                foreach ($_POST['new_variant_size'] as $key => $size) {
                    $color = $_POST['new_variant_color'][$key];
                    // Kiểm tra và gán giá trị mặc định nếu quantity trống hoặc không hợp lệ
                    $quantity = isset($_POST['new_variant_quantity'][$key]) && is_numeric($_POST['new_variant_quantity'][$key]) ? $_POST['new_variant_quantity'][$key] : 0;
                    

                    $this->productQuery->addProductVariants($id, $size, $color, $quantity);
                }
            }

            header('Location: index.php?action=product');  
        }

        // echo "<pre>";
        // print_r($_POST);
        // print_r($_FILES);
        // echo "</pre>";
        
        $listCategories = $this->productQuery->getAllCategories();
        $variant = $this->productQuery->getProductByVariant($id);
        $product = $this->productQuery->render_allproduct();
                
        include './views/admin/product/edit.php';
    }
    
    public function showsp(){
        $spmoi = $this->productQuery->render_allproduct();
    }

    public function updateProductStatus($product_id, $status) {
        if (isset($_POST['product_id']) && isset($_POST['new_status'])) {
            $product_id = (int)$_POST['product_id'];
            $new_status = (int)$_POST['new_status'];
        
            // Gọi hàm cập nhật trạng thái
            $result = $this->productQuery->updateStatus($product_id, $new_status);
        
            // Kiểm tra kết quả và thông báo
            // if ($result) {
            //     echo "<script>alert('Cập nhật trạng thái thành công!');</script>";
            // } else {
            //     echo "<script>alert('Cập nhật trạng thái thất bại.');</script>";
            // }
        
            // Chuyển hướng về danh sách sản phẩm
            header('Location: ?action=product');
            exit();
        }

    }
    
    
    // public function __destruct()
    //     {
    //         // Code...
    //     }

}
ob_end_flush();
?>
