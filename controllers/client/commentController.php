<?php
class commentController{
    public $commentModel;
    public function __construct()
    {
        $this->commentModel = new commentModel();
    }
    
    public function showComment()
    {
        $showComment = $this->commentModel->all();
        require_once './views/admin/comment/comment.php';
    }

    public function delete()
    {   
        $id = $_GET['id'];
        $this->commentModel->delete($id);
        header('location:?action=showComment');
    }
    
    public function comment()
    {   
        // var_dump($_POST);
        $comment = $_POST['comment'];
        $user_id = $_SESSION['name']['user_id'];
        $product_id = $_GET['product_id'];
        $create_at = date('Y-m-d H:i:s');
        $name = $_SESSION['name']['name'];
        $email = $_SESSION['name']['email'];
        $this->commentModel->insert_comment($comment,$user_id,$product_id,$create_at,$name,$email);
        header("location:?action=product-details&product_id=$product_id");
    }
}
?>