{extends file="findExtends:common/templates/listItem.tpl"}

{block name="itemLink"}
  {$listItemLabel}
  {if $item['url'] && $accessKey|default:false}
    {html_access_key_link href=$item['url'] class=$item['class']|default:null accessKey=false}
      {$item['title']}
    {/html_access_key_link}
    {$subtitleHTML}
    
  {else}
    {if $item['url']}
      <a href="{$item['url']}" class="{$item['class']|default:''}">
    {/if}
      <div class = "smallFont">{$item['title']}</div>
    {if $item['url']}
      </a>
    {/if}
    <!-- {if $item['sourceType']=="TwitterGeoSearch"}
      <span class="smallprint">
        {if $subTitleNewline|default:true}<br/>{else}&nbsp;{/if}
        {$item['content']}
      </span>
    {/if} -->
  {/if}
{/block}
