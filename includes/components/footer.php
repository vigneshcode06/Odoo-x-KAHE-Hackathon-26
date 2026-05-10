    </main>
    
    <?php if(!isset($hideFooter) || !$hideFooter): ?>
    <footer style="background: var(--secondary); color: white; padding: 3rem 0; margin-top: 4rem;">
        <div class="container" style="text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--accent);">Traveloop</div>
            <p style="color: #94A3B8; font-size: 0.875rem;">© <?php echo date('Y'); ?> Traveloop SaaS. Hackathon Edition.</p>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Global JS -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Page Specific Scripts -->
    <?php if(isset($pageScripts)): ?>
        <script>
            <?php echo $pageScripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>
