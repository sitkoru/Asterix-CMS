<p class="a_field">
<label for="{$field.sid}_day">{$field.title}:</label>
<select style="width:50px" name="{$field.sid}[day]" id="{$field.sid}_day">
	<option value="1"{if $field.value.day eq 1} selected="selected"{/if}>1</option>
	<option value="2"{if $field.value.day eq 2} selected="selected"{/if}>2</option>
	<option value="3"{if $field.value.day eq 3} selected="selected"{/if}>3</option>
	<option value="4"{if $field.value.day eq 4} selected="selected"{/if}>4</option>
	<option value="5"{if $field.value.day eq 5} selected="selected"{/if}>5</option>
	<option value="6"{if $field.value.day eq 6} selected="selected"{/if}>6</option>
	<option value="7"{if $field.value.day eq 7} selected="selected"{/if}>7</option>
	<option value="8"{if $field.value.day eq 8} selected="selected"{/if}>8</option>
	<option value="9"{if $field.value.day eq 9} selected="selected"{/if}>9</option>
	<option value="10"{if $field.value.day eq 10} selected="selected"{/if}>10</option>
	<option value="11"{if $field.value.day eq 11} selected="selected"{/if}>11</option>
	<option value="12"{if $field.value.day eq 12} selected="selected"{/if}>12</option>
	<option value="13"{if $field.value.day eq 13} selected="selected"{/if}>13</option>
	<option value="14"{if $field.value.day eq 14} selected="selected"{/if}>14</option>
	<option value="15"{if $field.value.day eq 15} selected="selected"{/if}>15</option>
	<option value="16"{if $field.value.day eq 16} selected="selected"{/if}>16</option>
	<option value="17"{if $field.value.day eq 17} selected="selected"{/if}>17</option>
	<option value="18"{if $field.value.day eq 18} selected="selected"{/if}>18</option>
	<option value="19"{if $field.value.day eq 19} selected="selected"{/if}>19</option>
	<option value="20"{if $field.value.day eq 20} selected="selected"{/if}>20</option>
	<option value="21"{if $field.value.day eq 21} selected="selected"{/if}>21</option>
	<option value="22"{if $field.value.day eq 22} selected="selected"{/if}>22</option>
	<option value="23"{if $field.value.day eq 23} selected="selected"{/if}>23</option>
	<option value="24"{if $field.value.day eq 24} selected="selected"{/if}>24</option>
	<option value="25"{if $field.value.day eq 25} selected="selected"{/if}>25</option>
	<option value="26"{if $field.value.day eq 26} selected="selected"{/if}>26</option>
	<option value="27"{if $field.value.day eq 27} selected="selected"{/if}>27</option>
	<option value="28"{if $field.value.day eq 28} selected="selected"{/if}>28</option>
	<option value="29"{if $field.value.day eq 29} selected="selected"{/if}>29</option>
	<option value="30"{if $field.value.day eq 30} selected="selected"{/if}>30</option>
	<option value="31"{if $field.value.day eq 31} selected="selected"{/if}>31</option>
</select>

<select style="width:100px" name="{$field.sid}[month]">
	<option value="1"{if $field.value.month eq 1} selected="selected"{/if}>января</option>
	<option value="2"{if $field.value.month eq 2} selected="selected"{/if}>февраля</option>
	<option value="3"{if $field.value.month eq 3} selected="selected"{/if}>марта</option>
	<option value="4"{if $field.value.month eq 4} selected="selected"{/if}>апреля</option>
	<option value="5"{if $field.value.month eq 5} selected="selected"{/if}>мая</option>
	<option value="6"{if $field.value.month eq 6} selected="selected"{/if}>июня</option>
	<option value="7"{if $field.value.month eq 7} selected="selected"{/if}>июля</option>
	<option value="8"{if $field.value.month eq 8} selected="selected"{/if}>августа</option>
	<option value="9"{if $field.value.month eq 9} selected="selected"{/if}>сентября</option>
	<option value="10"{if $field.value.month eq 10} selected="selected"{/if}>октября</option>
	<option value="11"{if $field.value.month eq 11} selected="selected"{/if}>ноября</option>
	<option value="12"{if $field.value.month eq 12} selected="selected"{/if}>декабря</option>
</select>

<select style="width:70px" name="{$field.sid}[year]">
	<option value="2000"{if $field.value.year eq 2000} selected="selected"{/if}>2000</option>
	<option value="2001"{if $field.value.year eq 2001} selected="selected"{/if}>2001</option>
	<option value="2002"{if $field.value.year eq 2002} selected="selected"{/if}>2002</option>
	<option value="2003"{if $field.value.year eq 2003} selected="selected"{/if}>2003</option>
	<option value="2004"{if $field.value.year eq 2004} selected="selected"{/if}>2004</option>
	<option value="2005"{if $field.value.year eq 2005} selected="selected"{/if}>2005</option>
	<option value="2006"{if $field.value.year eq 2006} selected="selected"{/if}>2006</option>
	<option value="2007"{if $field.value.year eq 2007} selected="selected"{/if}>2007</option>
	<option value="2008"{if $field.value.year eq 2008} selected="selected"{/if}>2008</option>
	<option value="2009"{if $field.value.year eq 2009} selected="selected"{/if}>2009</option>
	<option value="2010"{if $field.value.year eq 2010} selected="selected"{/if}>2010</option>
	<option value="2011"{if $field.value.year eq 2011} selected="selected"{/if}>2011</option>
	<option value="2012"{if $field.value.year eq 2012} selected="selected"{/if}>2012</option>
	<option value="2013"{if $field.value.year eq 2013} selected="selected"{/if}>2013</option>
	<option value="2014"{if $field.value.year eq 2014} selected="selected"{/if}>2014</option>
	<option value="2015"{if $field.value.year eq 2015} selected="selected"{/if}>2015</option>
</select>

<select style="margin-left:20px;width:50px" name="{$field.sid}[hour]">
	<option value="0"{if $field.value.hour eq 0} selected="selected"{/if}>0</option>
	<option value="1"{if $field.value.hour eq 1} selected="selected"{/if}>1</option>
	<option value="2"{if $field.value.hour eq 2} selected="selected"{/if}>2</option>
	<option value="3"{if $field.value.hour eq 3} selected="selected"{/if}>3</option>
	<option value="4"{if $field.value.hour eq 4} selected="selected"{/if}>4</option>
	<option value="5"{if $field.value.hour eq 5} selected="selected"{/if}>5</option>
	<option value="6"{if $field.value.hour eq 6} selected="selected"{/if}>6</option>
	<option value="7"{if $field.value.hour eq 7} selected="selected"{/if}>7</option>
	<option value="8"{if $field.value.hour eq 8} selected="selected"{/if}>8</option>
	<option value="9"{if $field.value.hour eq 9} selected="selected"{/if}>9</option>
	<option value="10"{if $field.value.hour eq 10} selected="selected"{/if}>10</option>
	<option value="11"{if $field.value.hour eq 11} selected="selected"{/if}>11</option>
	<option value="12"{if $field.value.hour eq 12} selected="selected"{/if}>12</option>
	<option value="13"{if $field.value.hour eq 13} selected="selected"{/if}>13</option>
	<option value="14"{if $field.value.hour eq 14} selected="selected"{/if}>14</option>
	<option value="15"{if $field.value.hour eq 15} selected="selected"{/if}>15</option>
	<option value="16"{if $field.value.hour eq 16} selected="selected"{/if}>16</option>
	<option value="17"{if $field.value.hour eq 17} selected="selected"{/if}>17</option>
	<option value="18"{if $field.value.hour eq 18} selected="selected"{/if}>18</option>
	<option value="19"{if $field.value.hour eq 19} selected="selected"{/if}>19</option>
	<option value="20"{if $field.value.hour eq 20} selected="selected"{/if}>20</option>
	<option value="21"{if $field.value.hour eq 21} selected="selected"{/if}>21</option>
	<option value="22"{if $field.value.hour eq 22} selected="selected"{/if}>22</option>
	<option value="23"{if $field.value.hour eq 23} selected="selected"{/if}>23</option>
</select>
<span> : </span>
<select style="width:50px" name="{$field.sid}[minute]">
	<option value="00"{if $field.value.minute eq 0} selected="selected"{/if}>00</option>
	<option value="01"{if $field.value.minute eq 1} selected="selected"{/if}>01</option>
	<option value="02"{if $field.value.minute eq 2} selected="selected"{/if}>02</option>
	<option value="03"{if $field.value.minute eq 3} selected="selected"{/if}>03</option>
	<option value="04"{if $field.value.minute eq 4} selected="selected"{/if}>04</option>
	<option value="05"{if $field.value.minute eq 5} selected="selected"{/if}>05</option>
	<option value="06"{if $field.value.minute eq 6} selected="selected"{/if}>06</option>
	<option value="07"{if $field.value.minute eq 7} selected="selected"{/if}>07</option>
	<option value="08"{if $field.value.minute eq 8} selected="selected"{/if}>08</option>
	<option value="09"{if $field.value.minute eq 9} selected="selected"{/if}>09</option>
	<option value="10"{if $field.value.minute eq 10} selected="selected"{/if}>10</option>
	<option value="11"{if $field.value.minute eq 11} selected="selected"{/if}>11</option>
	<option value="12"{if $field.value.minute eq 12} selected="selected"{/if}>12</option>
	<option value="13"{if $field.value.minute eq 13} selected="selected"{/if}>13</option>
	<option value="14"{if $field.value.minute eq 14} selected="selected"{/if}>14</option>
	<option value="15"{if $field.value.minute eq 15} selected="selected"{/if}>15</option>
	<option value="16"{if $field.value.minute eq 16} selected="selected"{/if}>16</option>
	<option value="17"{if $field.value.minute eq 17} selected="selected"{/if}>17</option>
	<option value="18"{if $field.value.minute eq 18} selected="selected"{/if}>18</option>
	<option value="19"{if $field.value.minute eq 19} selected="selected"{/if}>19</option>
	<option value="20"{if $field.value.minute eq 20} selected="selected"{/if}>20</option>
	<option value="21"{if $field.value.minute eq 21} selected="selected"{/if}>21</option>
	<option value="22"{if $field.value.minute eq 22} selected="selected"{/if}>22</option>
	<option value="23"{if $field.value.minute eq 23} selected="selected"{/if}>23</option>
	<option value="24"{if $field.value.minute eq 24} selected="selected"{/if}>24</option>
	<option value="25"{if $field.value.minute eq 25} selected="selected"{/if}>25</option>
	<option value="26"{if $field.value.minute eq 26} selected="selected"{/if}>26</option>
	<option value="27"{if $field.value.minute eq 27} selected="selected"{/if}>27</option>
	<option value="28"{if $field.value.minute eq 28} selected="selected"{/if}>28</option>
	<option value="29"{if $field.value.minute eq 29} selected="selected"{/if}>29</option>
	<option value="30"{if $field.value.minute eq 30} selected="selected"{/if}>30</option>
	<option value="31"{if $field.value.minute eq 31} selected="selected"{/if}>31</option>
	<option value="32"{if $field.value.minute eq 32} selected="selected"{/if}>32</option>
	<option value="33"{if $field.value.minute eq 33} selected="selected"{/if}>33</option>
	<option value="34"{if $field.value.minute eq 34} selected="selected"{/if}>34</option>
	<option value="35"{if $field.value.minute eq 35} selected="selected"{/if}>35</option>
	<option value="36"{if $field.value.minute eq 36} selected="selected"{/if}>36</option>
	<option value="37"{if $field.value.minute eq 37} selected="selected"{/if}>37</option>
	<option value="38"{if $field.value.minute eq 38} selected="selected"{/if}>38</option>
	<option value="39"{if $field.value.minute eq 39} selected="selected"{/if}>39</option>
	<option value="40"{if $field.value.minute eq 40} selected="selected"{/if}>40</option>
	<option value="41"{if $field.value.minute eq 41} selected="selected"{/if}>41</option>
	<option value="42"{if $field.value.minute eq 42} selected="selected"{/if}>42</option>
	<option value="43"{if $field.value.minute eq 43} selected="selected"{/if}>43</option>
	<option value="44"{if $field.value.minute eq 44} selected="selected"{/if}>44</option>
	<option value="45"{if $field.value.minute eq 45} selected="selected"{/if}>45</option>
	<option value="46"{if $field.value.minute eq 46} selected="selected"{/if}>46</option>
	<option value="47"{if $field.value.minute eq 47} selected="selected"{/if}>47</option>
	<option value="48"{if $field.value.minute eq 48} selected="selected"{/if}>48</option>
	<option value="49"{if $field.value.minute eq 49} selected="selected"{/if}>49</option>
	<option value="50"{if $field.value.minute eq 50} selected="selected"{/if}>50</option>
	<option value="51"{if $field.value.minute eq 51} selected="selected"{/if}>51</option>
	<option value="52"{if $field.value.minute eq 52} selected="selected"{/if}>52</option>
	<option value="53"{if $field.value.minute eq 53} selected="selected"{/if}>53</option>
	<option value="54"{if $field.value.minute eq 54} selected="selected"{/if}>54</option>
	<option value="55"{if $field.value.minute eq 55} selected="selected"{/if}>55</option>
	<option value="56"{if $field.value.minute eq 56} selected="selected"{/if}>56</option>
	<option value="57"{if $field.value.minute eq 57} selected="selected"{/if}>57</option>
	<option value="58"{if $field.value.minute eq 58} selected="selected"{/if}>58</option>
	<option value="59"{if $field.value.minute eq 59} selected="selected"{/if}>59</option>
</select>
</p>
