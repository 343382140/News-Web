
    [field:pagestyle runphp='yes'] @me= (@me==3 ? '
    <li>' : '');[/field:pagestyle] <a href='[field:linkurl/]' target='_blank'  [field:pagestyle runphp='yes'] @me= (@me==3 ? 'class="pic"' : ''); [/field:pagestyle]> <img src='[field:imgsrc/]' id='bigimg' [field:imgwidth /] alt='[field:alttext /]' border='0' /> </a>
      <!-- ���ʹ�õ��Ƕ�ҳ��ͼģʽ(�õ�)����href������Ӹ�Ϊ javascript:dPlayNext(); ��ʾ�������һҳ -->
      <a href="[field:linkurl/]" [field:pagestyle runphp='yes']@me = (@me==3 ? 'class="title"' : '');[/field:pagestyle]>  </a> [field:pagestyle runphp='yes'] @me = (@me==3 ? '</li>' : ''); [/field:pagestyle]
    