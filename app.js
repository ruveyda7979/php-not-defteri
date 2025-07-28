// ==================== NOT EKLEME FONKSİYONU ====================

/**
 * Yeni not eklemek için kullanılır
 * AJAX ile api.php dosyasına POST isteği gönderir
 */
function addNote() {
    // Form alanlarından değerleri al
    const title = document.getElementById('noteTitle').value;
    const content = document.getElementById('noteContent').value;
    const status = document.getElementById('noteStatus').value;
    const category = document.getElementById('noteCategory').value;
    const tags = document.getElementById('noteTags').value;
    
    // Başlık kontrolü - boş olamaz
    if (!title.trim()) {
        alert('Lütfen not başlığı girin!');
        return;  // Fonksiyonu durdur
    }
    
    // API'ye POST isteği gönder
    fetch('api.php?action=add_note', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}&status=${encodeURIComponent(status)}&category=${encodeURIComponent(category)}&tags=${encodeURIComponent(tags)}`
    })
    .then(response => response.json())  // JSON yanıtını çözümle
    .then(data => {
        if (data.success) {
            location.reload();  // Sayfa yenile (basit çözüm)
        } else {
            alert('Not eklenirken hata oluştu!');
        }
    })
    .catch(error => {
        console.error('Error:', error);  // Konsola hata yazdır
        alert('Bir hata oluştu!');
    });
}

// ==================== NOT SİLME FONKSİYONU ====================

/**
 * Not silmek için kullanılır
 * Önce kullanıcıdan onay alır, sonra AJAX ile siler
 */
function deleteNote(id) {
    // Kullanıcıdan onay al
    if (confirm('Notu silmek istediğinizden emin misiniz?')) {
        // API'ye silme isteği gönder
        fetch('api.php?action=delete_note', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`  // Not ID'sini gönder
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();  // Sayfa yenile
            } else {
                alert('Not silinirken hata oluştu!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu!');
        });
    }
}

// ==================== NOT DÜZENLEME FORMU ====================

/**
 * Not düzenleme formunu göster/gizle
 * Inline düzenleme için kullanılır
 */
function toggleEditForm(noteId) {
    const editForm = document.getElementById('edit-form-' + noteId);
    
    // Form görünür mü kontrolü
    if (editForm.style.display === 'none' || editForm.style.display === '') {
        editForm.style.display = 'block';   // Göster
    } else {
        editForm.style.display = 'none';    // Gizle
    }
}

// ==================== NOT GÜNCELLEME FONKSİYONU ====================

/**
 * Var olan notu günceller
 * Düzenleme formundaki değerleri alır ve API'ye gönderir
 */
function updateNote(noteId) {
    // Düzenleme formundaki değerleri al
    const title = document.getElementById('edit-title-' + noteId).value;
    const content = document.getElementById('edit-content-' + noteId).value;
    
    // Başlık kontrolü
    if (!title.trim()) {
        alert('Lütfen not başlığı girin!');
        return;
    }
    
    // API'ye güncelleme isteği gönder
    fetch('api.php?action=update_note', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${noteId}&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();  // Sayfa yenile
        } else {
            alert('Not güncellenirken hata oluştu!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu!');
    });
}

// ==================== SAYFA YÜKLENDİĞİNDE ÇALIŞIR ====================

/**
 * DOM tamamen yüklendiğinde çalışır
 * Event listener'ları ekler
 */
document.addEventListener('DOMContentLoaded', function() {
    // "Not Ekle" butonuna tıklama olayını dinle
    const addNoteBtn = document.getElementById('addNoteBtn');
    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', addNote);
    }
    
    // Başlık alanında Enter tuşuna basınca not ekle
    const noteTitle = document.getElementById('noteTitle');
    if (noteTitle) {
        noteTitle.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addNote();  // Enter'a basınca not ekle
            }
        });
    }
});

// ==================== LOADİNG GÖSTERGELERİ ====================

/**
 * Sayfa yüklenirken loading göstergesi
 * Mouse imlecini bekleme durumuna getirir
 */
function showLoading() {
    document.body.style.cursor = 'wait';    // Bekleme imleci
}

/**
 * Loading göstergesini kapat
 * Mouse imlecini normale döndür
 */
function hideLoading() {
    document.body.style.cursor = 'default';  // Normal imleç
}