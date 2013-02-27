{include file="findInclude:common/templates/header.tpl"}

<div id="background">
      <div id="category-switcher" class="category-mode">
        <form method="get" action="/{$configModule}/index" id="category-form">
              <div class="formlabel">{"SORT_TEXT"|getLocalizedString}
                <select class="sortinput" id="sort" name="sort" onchange="loadSection(this);">
                  <option value="{$section['value']}">Time</option>
                  <option value="{$section['value']}">Popularity</option>
                  <option value="{$section['value']}">Relevance</option>
                </select></div>
      </div>

{$defaultTemplateFile="findInclude:common/templates/listItem.tpl"}
{$listItemTemplateFile=$listItemTemplateFile|default:$defaultTemplateFile}

<ul class="results"{if $resultslistID} id="{$resultslistID}"{/if}>
  {foreach $tweetList as $item}
    {if !isset($item['separator'])}
      <li{if $item['img']} class="icon"{/if}>
        <div class= "ribbon">
        <div class="rectangle">? mins ago</div>
        <div class="r-triangle-top"></div>
        <div class="r-triangle-bottom"></div>
        </div>

        <table class="content" border="0">
        <tr>
        <td><img class= "thumbnail" src="/modules/buzz/images/placeholder.png"/></td>
        <td>
          {include file="$listItemTemplateFile" subTitleNewline=$subTitleNewline|default:true}
        </td>
        </table>
        
        
      </li>
    {/if}
  {/foreach}
  {if count($tweetList) == 0}
    {block name="noResults"}
      <li>{"NO_RESULTS"|getLocalizedString}</li>
    {/block}
  {/if}
</ul>

</div>
{include file="findInclude:common/templates/footer.tpl"}