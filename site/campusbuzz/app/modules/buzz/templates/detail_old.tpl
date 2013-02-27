{include file="findInclude:common/templates/header.tpl"}
  <select class="sortinput" id="sort" name="sort" onchange="loadSection(this);">
  	<option value="{$section['value']}">Time</option>
  	<option value="{$section['value']}">Popularity</option>

  </select>

<!-- {block name="detailHeader"} -->
  <!-- {if count($sections) > 1} -->
    <div class="header">
      <div id="category-switcher" class="category-mode">
        <form method="get" action="/{$configModule}/index" id="category-form">
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td class="formlabel">{"SORT_TEXT"|getLocalizedString}</td>
              <!-- <td class="inputfield"><div id="news-category-select">{$categorySelect}</div></td>
              <td class="togglefield">
                {block name="categoryButton"}
                  <input src="/common/images/search_button.png" type="image" class="toggle-search-button"  onclick="return toggleSearch();" width="32" height="30" />
                {/block} -->
              </td>
            </tr>
          </table>
          <!-- {foreach $hiddenArgs as $arg => $value}
            <input type="hidden" name="{$arg}" value="{$value|escape}" />
          {/foreach}
          {foreach $breadcrumbSamePageArgs as $arg => $value}
            <input type="hidden" name="{$arg}" value="{$value|escape}" />
          {/foreach} -->
        </form>
  
        
      </div>
    </div>
<!--   {else}
    {include file="findInclude:common/templates/search.tpl" extraArgs=$hiddenArgs}
  {/if} -->
<!-- {/block} -->

<h1 class="focal">{$message}</h1>

{include file="findInclude:common/templates/footer.tpl"}