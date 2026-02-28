    <footer class="footer" style="text-align:center; padding:3rem 1rem; border-top:1px solid var(--border-card); margin-top:4rem;">
      <div class="divider"><span>◆ — ◇ — ◆</span></div>
      <p style="font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.3em; color:var(--text-dim); text-transform:uppercase; margin-top:1rem;">
        The Archives of Clan Lar &nbsp;·&nbsp; @Vaelarn &nbsp;·&nbsp; PC–EU
      </p>
    </footer>
  </div><!-- .page-wrapper -->

  <!-- Search Overlay -->
  <div class="search-overlay" id="searchOverlay">
    <div class="search-overlay__inner">
      <input type="text" class="search-overlay__input" id="searchInput" placeholder="Search the Archives..." autocomplete="off">
      <div class="search-overlay__results" id="searchResults"></div>
      <div class="search-overlay__hint">
        <kbd>↑↓</kbd> Navigate &nbsp; <kbd>Enter</kbd> Go &nbsp; <kbd>Esc</kbd> Close
      </div>
    </div>
  </div>

  <script src="js/main.js"></script>
  <?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
