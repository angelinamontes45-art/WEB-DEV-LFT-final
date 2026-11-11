<?php
require '../_guard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $db = db();
    $check = $db->prepare('SELECT COUNT(*) FROM bookings WHERE space_id = ?');
    $check->execute([$id]);
    if ((int) $check->fetchColumn() > 0) {
        $disable = $db->prepare('UPDATE spaces SET active = 0 WHERE id = ?');
        $disable->execute([$id]);
        adminRedirect('spaces', 'message=' . urlencode('Space has bookings and was unpublished instead of deleted.'));
    }
    $delete = $db->prepare('DELETE FROM spaces WHERE id = ?');
    $delete->execute([$id]);
}
adminRedirect('spaces', 'message=' . urlencode('Space deleted.'));
