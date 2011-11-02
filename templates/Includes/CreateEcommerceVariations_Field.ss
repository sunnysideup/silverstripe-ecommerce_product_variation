<div id="CreateEcommerceVariationsInner" class="createEcommerceVariations">
	<h3><a href="#" id="StartCreateEcommerceVariationsField">Organise $ProductVariationGetPluralName</a></h3>
</div>
<div id="CreateEcommerceVariationsTemplate" class="createEcommerceVariations">
	<ul>
		<li class="messageHolder">
			<p id="InitMessage" class="message GOODORBAD">MESSAGE</p>
			<p id="MainReminderMessage" class="message good">Please make sure to click on the <i>create</i> button at the bottom of the list to finalise your selection.</p>
		</li>
		<li class="typeHolder">
			<div class="typeCheckHolder">
				<div class="checkboxInputHolder fieldHolder">
					<input type="checkbox" class="checkbox dataForType" id="TypeCheckID"  name="typeCheckID" value="ID" disabled="disabled" checked="checked" rel="ID" />
					<label>
						<a href="#" rel="TypeID" class="editNameLink dataForType">NAME</a>
						<a href="#" rel="ID" class="deleteLink  dataForType DELETE">delete</a>
					</label>
				</div>
				<div class="typeTextHolder textInputHolder fieldHolder editFieldHolder" id="editFieldForTypeID">
					<input type="text" class="text dataForType" id="typeTextID" value="NAME" name="typeTextID" rel="ID" />
				</div>
			</div>
			<ul class="valuesHolder">
				<li>VALUEHOLDER</li>
				<li class="valueAddHolder">
					$ValueSorterLink
					<label>
						<a href="#" class="addLabelLink">Add NAME</a>
					</label>
					<div class="textInputHolder addInputHolder">
						<input class="text dataForValue" id="valueAddID" value="" name="valueAddID" rel="ID" />
					</div>
				</li>
			</ul>
		</li>
		<li class="valueHolder">
			<div class="valueCheckHolder">
				<div class="checkboxInputHolder fieldHolder">
					<input type="checkbox" class="checkbox dataForValue" id="ValueCheckID"  name="valueCheckID" value="ID"  checked="checked" rel="ID" />
					<label>
						<a href="#" rel="ValueID" class="editNameLink dataForValue">NAME</a>
						<a href="#" rel="ID" class="deleteLink dataForValue DELETE">delete</a>
					</label>
				</div>
				<div class="valueTextHolder textInputHolder fieldHolder editFieldHolder" id="editFieldForValueID">
					<input type="text" class="text dataForValue" id="valueTextID" value="NAME" name="valueTextID" rel="ID" />
				</div>
			</div>
		</li>
		<li class="typeAddHolder">
			$AttributeSorterLink
			<label><a href="#" class="addLabelLink">Add $ProductAttributeTypeGetPluralName</a></label>
			<div class="textInputHolder addInputHolder">
				<input type="text" class="text dataForType" id="typeAdd" value="" name="typeAdd" rel="0" />
			</div>
		</li>
		<li class="typeAddFirstHolder">
			<p>No $ProductAttributeTypeGetPluralName have been added yet, please add below.</p>
		</li>
		<li class="createButtonHolder"><input type="submit" name="create" class="createButton" value="Create $ProductVariationGetPluralName" /></li>
	</ul>
</div>
