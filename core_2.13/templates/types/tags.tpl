<p>
{$field.title}:<br />
<textarea id="field_{$field.sid}" name="{$field.sid}" style="width:100%; height:50px;">{foreach from=$field.value item=val}{$val.title}, {/foreach}</textarea><br />
<i>Введите теги через запятую, например: [телефон, Wi-fi, мобичел]</i><br />

</p>
