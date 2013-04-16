{include file="findInclude:common/templates/header.tpl"}

<div id="background">
      <div id="category-switcher" class="category-mode">
        <form method="get" action="/{$configModule}/index" id="category-form">
              <div class="formlabel">
                <select class="sortinput" id="sort" name="sort" onchange="sortPosts(this);" data-index={$index} data-param={$params}>
                  <option value="sort">Sort Posts By:</option>
                  <option value="time">Time</option>
                  <option value="popularity">Popularity</option>
                </select></div>
      </div>

{$defaultTemplateFile="findInclude:common/templates/listItem.tpl"}
{$listItemTemplateFile=$listItemTemplateFile|default:$defaultTemplateFile}

<ul class="results"{if $resultslistID} id="{$resultslistID}"{/if}>

  <!-- <p class="smallprint">{$postList} </p> -->
  {foreach $postList as $item}
    
      <li>
        <div class= "ribbon">
        <div class="r-triangle-top"></div>
        <div class="r-triangle-bottom"></div>
        <div class="rectangle">{$item['pubDate']}</div>
        
        </div>

        <table class="content" border="0">
        <tr>
          <td class="imageCell">
            {if $item['imageUrl']}
              <img class= "thumbnail postImage" src={$item['imageUrl']}></img>
            {else}
              <img class= "thumbnail" src="/modules/buzz/images/placeholder.png"/>
            {/if}
          </td>

          <td>
            <a class="title" href={$item['url']}>{$item['title']}</a>
          </td>
        </tr>
        </table>
              
        {if $item['sourceType']=="TwitterGeoSearch"||$item['sourceType']=="Twitter"}
          <img class= "icon" src="/modules/buzz/images/icons/twitter_icon.png"/>
          {elseif $item['sourceType']=="Facebook"}
          <img class= "icon" src="/modules/buzz/images/icons/facebook-icon.png"/>
          {elseif $item['sourceType']=="RSS"}
          <img class= "icon" src="/modules/buzz/images/icons/feed-icon.png"/>
        {/if}

        <span class="smallprint authorField">
        Posted By: {$item['name']} @ {$item['locationName']}
        </span>
      </li>
  {/foreach}

  {if count($postList) == 0}
    {block name="noResults"}
      <li>{"NO_RESULTS"|getLocalizedString}</li>
    {/block}
  {/if}
</ul>
<div id="scrollText">Scroll Down To Load 10 More Posts. </div>

</div>
{include file="findInclude:common/templates/footer.tpl"}