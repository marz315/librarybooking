    </div>
    
    <!-- Secret Login Modal -->
    <div id="secretLoginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Akses Admin</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="secretKey">Kunci Rahsia</label>
                    <input type="password" id="secretKey" class="form-control" placeholder="Masukkan kunci rahsia...">
                </div>
                <div id="secretLoginMessage" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-danger close-modal">Batal</button>
                <button id="submitSecretKey" class="btn btn-success">Masuk</button>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistem Booking Perpustakaan Sekolah. Semua hak cipta terpelihara.</p>
        </div>
    </footer>

    <script>
        // Secret login dengan Ctrl+Alt+A
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.altKey && e.key === 'a') {
                e.preventDefault();
                document.getElementById('secretLoginModal').style.display = 'flex';
            }
        });

        // Tutup modal
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('secretLoginModal').style.display = 'none';
            });
        });

        // Submit secret key
        document.getElementById('submitSecretKey').addEventListener('click', function() {
            const secretKey = document.getElementById('secretKey').value;
            const messageDiv = document.getElementById('secretLoginMessage');
            
            fetch('admin-auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'secret_key=' + encodeURIComponent(secretKey)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = 'Login berhasil! Mengalihkan...';
                    setTimeout(() => {
                        window.location.href = 'admin.php';
                    }, 1000);
                } else {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'alert alert-error';
                    messageDiv.textContent = data.message || 'Kunci rahsia salah!';
                }
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-error';
                messageDiv.textContent = 'Ralat sistem!';
            });
        });

        // Enter key untuk submit
        document.getElementById('secretKey').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('submitSecretKey').click();
            }
        });
    </script>
</body>
</html>