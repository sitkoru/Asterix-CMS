{if $content.feedback.shw}
			<a name="feedback"></a>
			<form method="POST">{assign var=msg value=$messages.feedback}{assign var=mem value=$messages.feedback_memory}
				
				<input type="hidden" name="action" value="feedback" />{if $msg.ok}
				<div class="warning">{$msg.ok}</div>{/if}
				
{foreach from=$content.feedback.fields item=rec}{assign var=sid value=$rec.sid}{assign var=err value=$msg.$sid}{assign var=val value=$mem.$sid}
				{if $rec.type eq 'text'}
<div class="form-item form-cleared">
					<label for="id_{$rec.sid}">{if $rec.required}<span>*</span> {/if}{$rec.title}</label>
					<input{if $rec.required} class="input-text required"{/if} type="text" class="input-text" name="{$rec.sid}" id="id_{$rec.sid}" value="{$val}" />{if $err}
					<div class="warning">{$err}</div>{/if}

				</div>{elseif $rec.type eq 'textarea'}
<div class="textarea">
					<label for="id="id_{$rec.sid}">{if $rec.required}<span>*</span> {/if}{$rec.title}</label>
					<textarea{if $rec.required} class="required"{/if} name="{$rec.sid}" id="id_{$rec.sid}">{$val}</textarea>{if $err}
					<div class="warning">{$err}</div>{/if}

				</div>{/if}
				
{/foreach}
				<div class="buttons">
					<input type="submit" value="Отправить" class="submit" />{if $msg.ok}
					<div class="warning">{$msg.ok}</div>{/if}
					
				</div>
			</form>
{/if}
