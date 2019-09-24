<div class="new_lanmu_list_cnt MB10 clearfix">
      <div class="lanmu_title">
        <h3>{dede:field name='typename'/}</h3>
        <span class="lanmu_more"><a href="{dede:field name='typeurl'/}">¸ü¶à</a></span> </div>
      <div class="new_lanmu_cl l">
        <div class="new_lanmu_list clearfix l">
          <ul>
            {dede:arclist titlelen='48' row='6' limit='0,6'}
            <li class="first_news"><a href="[field:arcurl/]" title="[field:title /]">[field:title /]</a></li>
            {/dede:arclist}
          </ul>
          <ul>
            {dede:arclist titlelen='48' row='6' limit='6,6'}
            <li class="first_news"><a href="[field:arcurl/]" title="[field:title /]">[field:title /]</a></li>
            {/dede:arclist}
          </ul>
        </div>
        <div class="new_lanmu_mid clearfix r"> {dede:arclist titlelen='50' row='1' flag='c,p'} <a class="nl_m_p1" href="[field:arcurl/]" title='[field:title /]'> <img src="[field:litpic/]" alt="[field:title /]" width="275" height="160" /> <span>[field:title /]</span> </a> {/dede:arclist}
          <div class="nl_r_p_cnt"> {dede:arclist titlelen='50' row='2' flag='p' noflag='c'}
            <div class="nl_r_p_item"> <a href="[field:arcurl/]" title="[field:title /]"><img src="[field:litpic/]" alt="[field:title /]" width="125" height="90" original="[field:litpic/]"></a> <span>[field:title /]</span> </div>
            {/dede:arclist} </div>
        </div>
      </div>
    </div>