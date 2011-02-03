		</div>
		</div>
		<?php
			echo WW_getScripts();
			echo WW_getCSS();
			echo '<!-- page generated in '.(microtime()-$webme_start_time).' seconds -->';
		?>
		<script>if (!console) {console={log:function(){}}};</script>
	</body>
</html>
