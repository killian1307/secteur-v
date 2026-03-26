<?php
require_once 'assets/init_session.php';
require 'db.php';

// Vérification de la connexion et du grade
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$stmtUser = $pdo->prepare("SELECT grade FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();

$allowed_grades = ['Modérateur', 'Administrateur', 'Créateur'];

if (!$user || !in_array($user['grade'], $allowed_grades)) {
    require 'assets/header.php';
    $header = new Header("Accès Refusé");
    $header->render();
    echo "<main class='dashboard-container' style='text-align:center; padding: 5rem 0;'>
            <i class='fas fa-lock' style='font-size: 4rem; color: #e74c3c; margin-bottom: 1rem;'></i>
            <h1>Accès <span style='color:#e74c3c;'>Refusé</span></h1>
            <p>Seuls l'équipe du Secteur V peut écrire des articles.</p>
            <a href='index.php' class='other-button' style='margin-top: 2rem;'>Retour</a>
          </main>";
    require 'assets/footer.php';
    $footer = new Footer();
    $footer->render();
    exit;
}

$message = "";

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    // On force le slug en minuscules et on remplace les espaces par des tirets
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    $content = $_POST['content'];

    if (empty($title) || empty($slug) || empty($content)) {
        $message = "<div class='alert alert-danger' style='color:#e74c3c; margin-bottom: 15px;'>Tous les champs sont obligatoires.</div>";
    } else {
        // On vérifie si le slug existe déjà
        $stmtCheck = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmtCheck->execute([$slug]);
        if ($stmtCheck->fetch()) {
            $message = "<div class='alert alert-danger' style='color:#e74c3c; margin-bottom: 15px;'>Ce lien (slug) est déjà utilisé par un autre article.</div>";
        } else {
            // Insertion dans la base de données
            $insert = $pdo->prepare("INSERT INTO articles (author_id, title, slug, content) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$_SESSION['user_id'], $title, $slug, $content])) {
                $message = "<div class='alert alert-success' style='color:#2ecc71; margin-bottom: 15px;'>L'article a été publié avec succès !</div>";
            } else {
                $message = "<div class='alert alert-danger' style='color:#e74c3c; margin-bottom: 15px;'>Erreur lors de la publication.</div>";
            }
        }
    }
}

require 'assets/header.php';
require 'assets/footer.php';
$header = new Header("Écrire un article");
$header->render();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
  tinymce.init({
    selector: '#article-content',
    plugins: 'image link lists code table',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 500,
    // Configuration pour l'upload d'images
    images_upload_url: 'upload_article_image.php',
    images_upload_handler: function (blobInfo, progress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', 'upload_article_image.php');
            
            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) { reject({ message: 'HTTP Error: ' + xhr.status, remove: true }); return; }
                if (xhr.status < 200 || xhr.status >= 300) { reject('HTTP Error: ' + xhr.status); return; }
                const json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') { reject('Invalid JSON: ' + xhr.responseText); return; }
                resolve(json.location);
            };

            xhr.onerror = () => { reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status); };

            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            // On envoie aussi le slug pour savoir dans quel dossier ranger l'image
            formData.append('slug', document.getElementById('slug').value);

            xhr.send(formData);
        });
    }
  });

  // Petit script pour générer le lien (slug) automatiquement en tapant le titre
  function generateSlug() {
      const title = document.getElementById('title').value;
      const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
      document.getElementById('slug').value = slug;
  }
</script>

<main class="dashboard-container">
    <h1>write an <span style="color:var(--primary-purple)">Article</span></h1>
    
    <div class="profile-card" style="max-width: 900px; margin: 2rem auto; text-align: left;">
        <?php echo $message; ?>
        
        <form method="POST" action="write_article.php" class="profile-form">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Title *</label>
                <input type="text" id="title" name="title" required placeholder="Ex: The new meta of Inazuma" onkeyup="generateSlug()" style="width:100%; padding:10px; border-radius:5px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:white;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Article Link (URL) *</label>
                <input type="text" id="slug" name="slug" required placeholder="ex: the-new-meta" style="width:100%; padding:10px; border-radius:5px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); color:#aaa;">
                <small style="color:#aaa;">This is the name of the folder that will be created to store the images.</small>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Content *</label>
                <textarea id="article-content" name="content"></textarea>
            </div>

            <button type="submit" class="other-button" style="width: 100%; justify-content: center;">
                <i class="fas fa-paper-plane"></i> Publish Article
            </button>
        </form>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>