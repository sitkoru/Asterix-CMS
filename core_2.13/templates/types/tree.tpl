  <label for="field_{$field.dep_path_name}">{$field.title}:</label>
  <select name="{$field.dep_path_name}" id="field_{$field.dep_path_name}">
  {foreach from=$field.value item=val}
    <option value="{$val.sid}"{if $val.selected}  style="background-color:#39f; color:#fff;"{/if}{if $val.disabled} disabled="disabled"{/if}{if $val.selected} selected="selected"{/if}>
    {section name=pre start=1 loop=$val.tree_level max=$val.tree_level}
      &nbsp;&nbsp;&nbsp;&nbsp;|
      {/section}
      {$val.title}
    </option>
  {/foreach}
  </select>
