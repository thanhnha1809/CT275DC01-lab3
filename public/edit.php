<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

$id = isset($_REQUEST['id']) ?
    filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

if (!$id || !($contact->find($id))) {
    redirect('/');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    $errors = $contact->validate($contactData);
    if (empty($errors)) {
        $contact->fill($contactData);
        $contact->handleUpload($_FILES['avatar'] ?? []);
        $contact->save() && redirect('/');
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'Update your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <form method="post" enctype="multipart/form-data" class="col-md-6 offset-md-3">

          <input type="hidden" name="id" value="<?= $contact->id ?>">

          <!-- Name -->
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" maxlen="255" id="name" placeholder="Enter Name" value="<?= html_escape($contact->name) ?>" />

            <?php if (isset($errors['name'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['name'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" maxlen="255" id="phone" placeholder="Enter Phone" value="<?= html_escape($contact->phone) ?>" />

            <?php if (isset($errors['phone'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['phone'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label for="notes" class="form-label">Notes </label>
            <textarea name="notes" id="notes" class="form-control<?= isset($errors['notes']) ? ' is-invalid' : '' ?>" placeholder="Enter notes (maximum character limit: 255)"><?= html_escape($contact->notes) ?></textarea>

            <?php if (isset($errors['notes'])) : ?>
              <span class="invalid-feedback">
                <strong><?= $errors['notes'] ?></strong>
              </span>
            <?php endif ?>
          </div>

          <!-- Avatar Upload -->
          <div class="mb-3">
            <label for="avatar" class="form-label">Avatar</label>
            <?php if (!empty($contact->avatar)): ?>
              <div class="mb-2">
                <img src="/uploads/<?= html_escape($contact->avatar) ?>" alt="Current Avatar" style="max-height: 80px;" class="rounded">
              </div>
            <?php endif; ?>
            <input type="file" name="avatar" class="form-control" id="avatar" accept="image/*">
            <div class="mt-2">
                <img id="avatar-preview" src="#" alt="Preview" style="max-height: 120px; display: none;" class="rounded">
            </div>
          </div>

          <!-- Submit -->
          <button type="submit" name="submit" class="btn btn-primary">Update Contact</button>
          <a href="/" class="btn btn-secondary">Cancel</a>
        </form>

      </div>
    </div>

  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>
  <script>
    document.getElementById('avatar').addEventListener('change', function(e) {
        const [file] = e.target.files;
        if (file) {
            const preview = document.getElementById('avatar-preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
  </script>
</body>

</html>