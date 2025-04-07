document.addEventListener("DOMContentLoaded", function () {
    const fileList = document.getElementById("file-list");

     fetch("get_files.php")
        .then(response => response.json())
        .then(data => {
            data.forEach(file => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                    <a href="${file.file_path}" download>${file.file_name}</a>
                    <button class="delete-btn" data-id="${file.id}">Sil</button>
                `;
                fileList.appendChild(listItem);
            });

             const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const fileId = this.getAttribute('data-id');
                    if (confirm("Bu dosyayı silmek istediğinizden emin misiniz?")) {
                        window.location.href = `delete_file.php?id=${fileId}`;
                    }
                });
            });
        })
        .catch(error => console.error("Hata:", error));
});
 
function downloadFile(fileUrl) {
    window.location.href = "download_file.php?id=" + fileUrl; 
}
 
function deleteFile(fileUrl) {
    if (confirm("Dosyayı silmek istediğinize emin misiniz?")) { 
        window.location.href = 'delete_file.php?id=' + fileUrl; 
    }
}
 
function shareFile(fileUrl) {
    const shareLink = window.location.href + "share_file.php?id=" + fileUrl;  
    prompt("Paylaşmak için linki kopyalayın:", shareLink);
}
window.addEventListener("load", function () {
    document.body.classList.add("loaded");
});
