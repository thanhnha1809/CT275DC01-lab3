<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;
use CT275\Labs\Paginator;

$contact = new Contact($PDO);

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit'])) ?
    (int)$_GET['limit'] : 5;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ?
    (int)$_GET['page'] : 1;
$paginator = new Paginator(
    totalRecords: $contact->count(),
    recordsPerPage: $limit,
    currentPage: $page
);
$contacts = $contact->paginate($paginator->recordOffset, $paginator->recordsPerPage);
$pages = $paginator->getPages(length: 3);

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'View your all contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <a href="/add.php" class="btn btn-primary mb-3">
          <i class="fa fa-plus"></i> New Contact
        </a>

        <!-- Table Starts Here -->
        <table id="contacts" class="table table-striped table-bordered align-middle">
          <thead>
            <tr>
              <th scope="col" style="width: 80px;">Avatar</th>
              <th scope="col">Name</th>
              <th scope="col">Phone</th>
              <th scope="col">Date Created</th>
              <th scope="col">Notes</th>
              <th scope="col" style="width: 150px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($contacts)): ?>
              <?php foreach ($contacts as $contact): ?>
                <tr>
                  <td class="text-center">
                    <?php if (!empty($contact->avatar)): ?>
                      <img src="/uploads/<?= html_escape($contact->avatar) ?>" alt="Avatar" width="40" height="40" class="rounded-circle" style="object-fit: cover;">
                    <?php else: ?>
                      <img src="https://via.placeholder.com/40" alt="No Image" class="rounded-circle">
                    <?php endif; ?>
                  </td>
                  <td><?= html_escape($contact->name) ?></td>
                  <td><?= html_escape($contact->phone) ?></td>
                  <td><?= html_escape(date("d-m-Y", strtotime($contact->created_at))) ?></td>
                  <td><?= html_escape($contact->notes) ?></td>
                  <td class="d-flex justify-content-center align-items-center">
                    <a href="/edit.php?id=<?= $contact->id ?>" class="btn btn-xs btn-warning">
                      <i alt="Edit" class="fa fa-pencil"></i> Edit
                    </a>
                    
                    <!-- Form xóa dùng POST gửi tới delete.php -->
                    <form class="ms-1 mb-0" action="/delete.php" method="POST">
                      <input type="hidden" name="id" value="<?= $contact->id ?>">
                      <button type="submit" class="btn btn-xs btn-danger" name="delete-contact">
                        <i alt="Delete" class="fa fa-trash"></i> Delete
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center">Không có liên hệ nào.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <!-- Table Ends Here -->

        <!-- Pagination -->
        <nav class="d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item <?= $paginator->getPrevPage() ? '' : 'disabled' ?>">
              <a role="button"
                href="/?page=<?= $paginator->getPrevPage() ?>&limit=5"
                class="page-link">
                <span>&laquo;</span>
              </a>
            </li>
            <?php foreach ($pages as $page) : ?>
              <li class="page-item <?= $paginator->currentPage === $page ? 'active' : '' ?>">
                <a role="button" href="/?page=<?= $page ?>&limit=5"
                  class="page-link"><?= $page ?></a>
              </li>
            <?php endforeach ?>
            <li class="page-item <?= $paginator->getNextPage() ? '' : 'disabled' ?>">
              <a role="button"
                href="/?page=<?= $paginator->getNextPage() ?>&limit=5"
                class="page-link">
                <span>&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="delete-confirm" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">Do you want to delete this contact?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="delete">Delete</button>
          <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>

  <!-- JavaScript logic -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let currentFormToDelete = null;

      // Lấy danh sách nút Delete trong bảng
      const deleteButtons = document.querySelectorAll('button[name="delete-contact"]');
      deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
          e.preventDefault();

          // Lưu form tương ứng với nút vừa bấm
          currentFormToDelete = button.closest('form');

          // Lấy tên liên hệ ở cột Name (cột thứ 2, index 1 hoặc điều chỉnh theo cấu trúc tr)
          const row = button.closest('tr');
          const nameTd = row ? row.querySelectorAll('td')[1] : null;
          if (nameTd) {
            document.querySelector('.modal-body').textContent = 
              `Do you want to delete "${nameTd.textContent.trim()}"?`;
          }

          // Hiển thị Bootstrap Modal
          const modalEl = document.getElementById('delete-confirm');
          const confirmModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: 'static',
            keyboard: false
          });
          confirmModal.show();
        });
      });

      // Lắng nghe sự kiện click nút Delete trong Modal (chỉ gán 1 lần)
      const modalDeleteBtn = document.getElementById('delete');
      if (modalDeleteBtn) {
        modalDeleteBtn.addEventListener('click', function () {
          if (currentFormToDelete) {
            currentFormToDelete.submit();
          }
        });
      }
    });
  </script>
</body>

</html>