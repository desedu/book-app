<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <?php
                if (isset($_SESSION['msg']) && $_SESSION['msg'] != '') {
                    echo htmlspecialchars($_SESSION['msg']);
                    $_SESSION['msg'] = '';
                }
                if (isset($msg) && $msg != '') {
                    echo htmlspecialchars($msg);
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('messageModal'), {});
    myModal.show();
</script>