<?php
require_once __DIR__ . '/comman/auth.php';
$pageTitle = "System & Website Settings";
require __DIR__ . '/comman/header.php';
require __DIR__ . '/comman/sidebar.php';

$site_settings_file = CMS_DATA . '/site_settings.json';
$site_settings = read_json($site_settings_file, [
    'siteName' => 'YonoLootZone',
    'telegramUrl' => 'https://t.me/allyonolootzone',
    'whatsappUrl' => 'https://wa.me/919999999999',
    'contactEmail' => 'contact@yonolootzone.com',
    'announcement' => '73 verified Yono games, updated APK guides, and ratings.',
    'accentColor' => '#e00068'
]);

$logs = read_json(CMS_DATA . '/activity_log.json', []);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (isset($_POST['update_general'])) {
        $site_settings['siteName'] = post_str('siteName', 'YonoLootZone');
        $site_settings['telegramUrl'] = safe_url(post_str('telegramUrl'));
        $site_settings['whatsappUrl'] = safe_url(post_str('whatsappUrl'));
        $site_settings['contactEmail'] = post_str('contactEmail');
        $site_settings['announcement'] = post_str('announcement');
        write_json($site_settings_file, $site_settings);

        // Log action
        array_unshift($logs, [
            'action' => 'Updated Site Settings',
            'user' => $_SESSION['cms_user'] ?? 'Admin',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'time' => date('Y-m-d H:i:s')
        ]);
        write_json(CMS_DATA . '/activity_log.json', array_slice($logs, 0, 50));

        flash('success', 'General website settings updated successfully.');
        redirect_admin('settings.php');
    }

    if (isset($_POST['change_password'])) {
        $p = $_POST['new_password'] ?? '';
        $c = $_POST['confirm_password'] ?? '';
        if ($p === '') {
            flash('info', 'No password change requested.');
        } elseif (strlen($p) < 10 || $p !== $c) {
            flash('danger', 'Password must be at least 10 characters and match.');
        } else {
            $auth = read_json(CMS_DATA . '/auth.json', []);
            $auth['password_hash'] = password_hash($p, PASSWORD_DEFAULT);
            $auth['updated_at'] = date('c');
            write_json(CMS_DATA . '/auth.json', $auth);

            array_unshift($logs, [
                'action' => 'Changed Admin Password',
                'user' => $_SESSION['cms_user'] ?? 'Admin',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'time' => date('Y-m-d H:i:s')
            ]);
            write_json(CMS_DATA . '/activity_log.json', array_slice($logs, 0, 50));

            flash('success', 'Admin master password changed successfully.');
        }
        redirect_admin('settings.php');
    }
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-8">
          <h1 class="font-weight-bold"><?= h($pageTitle) ?></h1>
          <div class="text-muted">Manage website contact info, public announcement banner, Telegram/WhatsApp links, and security.</div>
        </div>
      </div>
      <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash[0]) ?> alert-dismissible fade show">
          <?= h($flash[1]) ?>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        
        <!-- General Website & Communication Settings -->
        <div class="col-lg-7">
          <div class="card card-outline card-danger shadow-sm" style="border-radius:16px;">
            <div class="card-header font-weight-bold">
              <i class="fas fa-globe mr-2 text-danger"></i> Public Website & Communication Settings
            </div>
            <div class="card-body">
              <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
                <input type="hidden" name="update_general" value="1">

                <div class="form-group">
                  <label class="font-weight-bold">Site Brand Name</label>
                  <input type="text" name="siteName" class="form-control" value="<?= h($site_settings['siteName'] ?? 'YonoLootZone') ?>" required>
                </div>

                <div class="form-group">
                  <label class="font-weight-bold">Top Announcement Banner</label>
                  <input type="text" name="announcement" class="form-control" value="<?= h($site_settings['announcement'] ?? '') ?>">
                  <small class="text-muted">Displays at the top of all public pages. Visitors can dismiss it.</small>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="font-weight-bold"><i class="fab fa-telegram-plane text-info mr-1"></i> Telegram Link</label>
                      <input type="url" name="telegramUrl" class="form-control" value="<?= h($site_settings['telegramUrl'] ?? '') ?>" placeholder="https://t.me/yourchannel">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="font-weight-bold"><i class="fab fa-whatsapp text-success mr-1"></i> WhatsApp Support Link</label>
                      <input type="text" name="whatsappUrl" class="form-control" value="<?= h($site_settings['whatsappUrl'] ?? '') ?>" placeholder="https://wa.me/919999999999">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="font-weight-bold"><i class="fas fa-envelope mr-1"></i> Official Contact Email</label>
                  <input type="email" name="contactEmail" class="form-control" value="<?= h($site_settings['contactEmail'] ?? '') ?>">
                </div>

                <button class="btn btn-pink font-weight-bold px-4">
                  <i class="fas fa-save mr-1"></i> Save Website Settings
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Security & Admin Password -->
        <div class="col-lg-5">
          <div class="card card-outline card-secondary shadow-sm" style="border-radius:16px;">
            <div class="card-header font-weight-bold">
              <i class="fas fa-shield-alt mr-2 text-primary"></i> Admin Password & Security
            </div>
            <div class="card-body">
              <form method="post">
                <input type="hidden" name="csrf" value="<?= h(csrf()) ?>">
                <input type="hidden" name="change_password" value="1">

                <div class="form-group">
                  <label class="font-weight-bold">New Master Password</label>
                  <input type="password" name="new_password" class="form-control" minlength="10" placeholder="Minimum 10 characters" required>
                </div>

                <div class="form-group">
                  <label class="font-weight-bold">Confirm Master Password</label>
                  <input type="password" name="confirm_password" class="form-control" minlength="10" placeholder="Repeat new password" required>
                </div>

                <button class="btn btn-dark font-weight-bold btn-block">
                  <i class="fas fa-key mr-1"></i> Update Admin Password
                </button>
              </form>
            </div>
          </div>

          <!-- Activity Logs Preview -->
          <div class="card shadow-sm" style="border-radius:16px;">
            <div class="card-header font-weight-bold">
              <i class="fas fa-history mr-2 text-warning"></i> Recent Admin Activity
            </div>
            <div class="card-body p-0" style="max-height:240px; overflow-y:auto;">
              <table class="table table-sm table-striped mb-0" style="font-size:12px;">
                <thead>
                  <tr>
                    <th>Action</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$logs): ?>
                    <tr><td colspan="2" class="text-center text-muted py-3">No recent logs recorded.</td></tr>
                  <?php else: ?>
                    <?php foreach (array_slice($logs, 0, 10) as $log): ?>
                      <tr>
                        <td><strong><?= h($log['action'] ?? 'Action') ?></strong> <small class="text-muted d-block"><?= h($log['ip'] ?? '') ?></small></td>
                        <td class="text-muted"><?= h($log['time'] ?? '') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>
    </div>
  </section>
</div>
<?php require __DIR__ . '/comman/footer.php'; ?>
