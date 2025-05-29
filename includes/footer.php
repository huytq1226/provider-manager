    </main>
    
    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Provider Management System</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Version 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar on mobile
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        });
    </script>

    <!-- Notification JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastContainer = document.getElementById('notificationToast');
            
            // Function to create and display a toast notification
            function showNotificationToast(title, message, severity) {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = 'toast show';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                
                // Set background color based on severity
                let bgClass = 'bg-info text-dark';
                let iconClass = 'fa-info-circle';
                
                if (severity === 'urgent') {
                    bgClass = 'bg-danger text-white';
                    iconClass = 'fa-exclamation-circle';
                } else if (severity === 'warning') {
                    bgClass = 'bg-warning text-dark';
                    iconClass = 'fa-exclamation-triangle';
                }
                
                // Create toast content
                toast.innerHTML = `
                    <div class="toast-header ${bgClass}">
                        <i class="fas ${iconClass} me-2"></i>
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                `;
                
                // Add toast to container
                toastContainer.appendChild(toast);
                
                // Remove toast after 5 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 500);
                }, 5000);
            }
            
            // Check for urgent notifications and show them as toasts
            <?php
            // Only include this if notifications.php exists and we have unread notifications
            if (isset($unreadNotifications) && !empty($unreadNotifications)) {
                // Show only urgent notifications as toasts
                foreach ($unreadNotifications as $notification) {
                    if ($notification['severity'] === 'urgent') {
                        echo "showNotificationToast('".addslashes($notification['title'])."', '".addslashes($notification['message'])."', 'urgent');\n";
                    }
                }
            }
            ?>
            
            // Handle AJAX form submissions for notification actions
            document.querySelectorAll('.notification-actions form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('process_notification.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove notification from the list
                            const notificationItem = this.closest('.notification-item');
                            if (notificationItem) {
                                notificationItem.remove();
                            }
                            
                            // Update badge count
                            const badge = document.querySelector('.bell-badge');
                            if (data.unread_count > 0) {
                                if (badge) {
                                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                                } else {
                                    const bellContainer = document.querySelector('.bell-icon-container');
                                    if (bellContainer) {
                                        const newBadge = document.createElement('span');
                                        newBadge.className = 'badge bg-danger bell-badge';
                                        newBadge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                                        bellContainer.appendChild(newBadge);
                                    }
                                }
                            } else {
                                if (badge) {
                                    badge.remove();
                                }
                                
                                // If no more notifications, show empty state
                                const notificationItems = document.querySelectorAll('.notification-item');
                                if (notificationItems.length === 0) {
                                    const dropdown = document.querySelector('.notification-dropdown');
                                    if (dropdown) {
                                        const header = dropdown.querySelector('.notification-header');
                                        const emptyState = document.createElement('div');
                                        emptyState.className = 'no-notifications';
                                        emptyState.innerHTML = `
                                            <i class="fas fa-bell-slash fa-2x mb-3 text-muted"></i>
                                            <p>Không có thông báo mới</p>
                                        `;
                                        
                                        // Insert after header
                                        if (header) {
                                            header.insertAdjacentElement('afterend', emptyState);
                                        } else {
                                            dropdown.appendChild(emptyState);
                                        }
                                    }
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html> 