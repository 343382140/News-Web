<!-- 元素开始 -->
<div id="tabBox2" class="tabBox">
  <div class="hd">
    <h3><a href="list.php?tid={dede:field name='id'/}" >{dede:field name='typename'/}</a></h3>
    <ul>
      <li><a href="list.php?tid={dede:field name='id'/}">更多</a></li>
    </ul>
  </div>
  <div class="bd">
      <ul>
        {dede:arclist titlelen='40' row='7'}
        <li><a href="view.php?aid=[field:id/]" title="[field:title /]">[field:title /]</a></li>
         {/dede:arclist}
      </ul>
  </div>
</div>