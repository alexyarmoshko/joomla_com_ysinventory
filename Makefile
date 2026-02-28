# Joomla Component Makefile — com_ysinventory
# Derived from a component Makefile template

COMPONENT_NAME := com_ysinventory
MANIFEST := ysinventory.xml
UPDATE_XML := com_ysinventory.update.xml
INSTALL_DIR ?= installation

VERSION := $(shell awk -F'[<>]' '/<version>/{print $$3; exit}' "$(MANIFEST)")
ZIP_VERSION := $(subst .,-,$(VERSION))
ZIP_NAME := $(COMPONENT_NAME)-v$(ZIP_VERSION).zip
ZIP_PATH := $(INSTALL_DIR)/$(ZIP_NAME)

PACKAGE_FILES := $(MANIFEST) LICENSE admin site media

.PHONY: info dist clean

info:
	@echo "Component:       $(COMPONENT_NAME)"
	@echo "Version:         $(VERSION)"
	@echo "Package files:   $(PACKAGE_FILES)"
	@echo "Package output:  $(ZIP_PATH)"

dist: $(ZIP_PATH)

$(ZIP_PATH): $(PACKAGE_FILES)
	@mkdir -p "$(INSTALL_DIR)"
	@rm -f "$(ZIP_PATH)"
	@cd "$(CURDIR)" && zip -r -X "$(ZIP_PATH)" $(PACKAGE_FILES) -x "*.DS_Store" -x "*/.DS_Store"
	@echo "Built $(ZIP_PATH)"

clean:
	@rm -f "$(ZIP_PATH)"
