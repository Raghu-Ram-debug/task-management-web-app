<?php
/**
 * Footer Template
 * Task Manager Application
 */
?>
<?php if (isLoggedIn()): ?>
            </div>
        </main>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="../js/script.js"></script>
</body>
</html>
<?php else: ?>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
<?php endif; ?>