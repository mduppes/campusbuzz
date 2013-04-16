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
            {if $item['title'] != $item['content']}
              <a  href={$item['url']}>{$item['title']}</a>
              {if isset($item['content'])}
                <div class="smallprint">{$item['content']}</div>
              {/if}
            {else}
              <a class="title" href={$item['url']}>{$item['title']}</a>
            {/if}

            {if $item['sourceType']=="RSSEvents"}
              <div class="date_text">Start Time: {$item['startDate']}</div>
              <div class="date_text">End Time: {$item['endDate']}</div>
            {/if}
          </td>
        </tr>
        </table>
              
        {if $item['sourceType']=="TwitterGeoSearch"||$item['sourceType']=="Twitter"}
          <img class= "icon" src="/modules/buzz/images/icons/twitter_icon.png"/>
          {elseif $item['sourceType']=="Facebook"}
          <img class= "icon" src="/modules/buzz/images/icons/facebook-icon.png"/>
          {elseif $item['sourceType']=="RSS"}
          <img class= "icon" src="/modules/buzz/images/icons/feed-icon.png"/>
          {else if $item['sourceType']=="RSSEvents"}
          <img class= "icon" src="/modules/buzz/images/icons/event-icon.png"/>
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
<div id="scrollText" style="color: white; padding-left: 20%;">Scroll To Load 10 More Posts... </div>

</div>
{include file="findInclude:common/templates/footer.tpl"}