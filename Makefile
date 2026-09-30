# Reproducible packaging for com_ysinventory: the ZIP bytes depend only on the
# packaged files and one timestamp, never on the machine that built it, so a release
# can be rebuilt from its tag and still hash to the sha256 its update descriptor
# claims.

SHELL := /bin/sh

EXT_NAME := com_ysinventory
MANIFEST := ysinventory.xml
GITHUB_OWNER := alexyarmoshko
GITHUB_REPO := joomla_com_ysinventory

# One explicit list of what ships. Never a directory: a new source file is left out
# until it is added here - check `make info` after adding one. media/ has no files yet;
# Joomla skips an empty <media> element.
PACKAGE_FILES := \
	LICENSE \
	$(MANIFEST) \
	admin/access.xml \
	admin/config.xml \
	admin/forms/brand.xml \
	admin/forms/category.xml \
	admin/forms/filter_brands.xml \
	admin/forms/filter_categories.xml \
	admin/forms/filter_inventories.xml \
	admin/forms/filter_items.xml \
	admin/forms/filter_lends.xml \
	admin/forms/filter_taggroups.xml \
	admin/forms/filter_tags.xml \
	admin/forms/inventory.xml \
	admin/forms/item.xml \
	admin/forms/lend.xml \
	admin/forms/taggroup.xml \
	admin/forms/tag.xml \
	admin/language/en-GB/com_ysinventory.ini \
	admin/language/en-GB/com_ysinventory.sys.ini \
	admin/script.php \
	admin/services/provider.php \
	admin/sql/install.mysql.utf8mb4.sql \
	admin/sql/uninstall.mysql.utf8mb4.sql \
	admin/sql/updates/mysql/1.0.0.sql \
	admin/src/Controller/BrandController.php \
	admin/src/Controller/BrandsController.php \
	admin/src/Controller/CategoriesController.php \
	admin/src/Controller/CategoryController.php \
	admin/src/Controller/DisplayController.php \
	admin/src/Controller/InventoriesController.php \
	admin/src/Controller/InventoryController.php \
	admin/src/Controller/ItemController.php \
	admin/src/Controller/ItemsController.php \
	admin/src/Controller/LendController.php \
	admin/src/Controller/LendsController.php \
	admin/src/Controller/TagController.php \
	admin/src/Controller/TaggroupController.php \
	admin/src/Controller/TaggroupsController.php \
	admin/src/Controller/TagsController.php \
	admin/src/Extension/YsinventoryComponent.php \
	admin/src/Helper/ModeratorHelper.php \
	admin/src/Helper/StatusHelper.php \
	admin/src/Model/BrandModel.php \
	admin/src/Model/BrandsModel.php \
	admin/src/Model/CategoriesModel.php \
	admin/src/Model/CategoryModel.php \
	admin/src/Model/InventoriesModel.php \
	admin/src/Model/InventoryModel.php \
	admin/src/Model/ItemModel.php \
	admin/src/Model/ItemsModel.php \
	admin/src/Model/LendModel.php \
	admin/src/Model/LendsModel.php \
	admin/src/Model/TaggroupModel.php \
	admin/src/Model/TaggroupsModel.php \
	admin/src/Model/TagModel.php \
	admin/src/Model/TagsModel.php \
	admin/src/Table/BrandTable.php \
	admin/src/Table/CategoryTable.php \
	admin/src/Table/InventoryTable.php \
	admin/src/Table/ItemTable.php \
	admin/src/Table/LendTable.php \
	admin/src/Table/TaggroupTable.php \
	admin/src/Table/TagTable.php \
	admin/src/View/Brand/HtmlView.php \
	admin/src/View/Brands/HtmlView.php \
	admin/src/View/Categories/HtmlView.php \
	admin/src/View/Category/HtmlView.php \
	admin/src/View/Inventories/HtmlView.php \
	admin/src/View/Inventory/HtmlView.php \
	admin/src/View/Item/HtmlView.php \
	admin/src/View/Items/HtmlView.php \
	admin/src/View/Lend/HtmlView.php \
	admin/src/View/Lends/HtmlView.php \
	admin/src/View/Taggroup/HtmlView.php \
	admin/src/View/Taggroups/HtmlView.php \
	admin/src/View/Tag/HtmlView.php \
	admin/src/View/Tags/HtmlView.php \
	admin/src/View/Ysinventory/HtmlView.php \
	admin/tmpl/brand/edit.php \
	admin/tmpl/brands/default.php \
	admin/tmpl/brands/emptystate.php \
	admin/tmpl/categories/default.php \
	admin/tmpl/categories/emptystate.php \
	admin/tmpl/category/edit.php \
	admin/tmpl/inventories/default.php \
	admin/tmpl/inventories/emptystate.php \
	admin/tmpl/inventory/edit.php \
	admin/tmpl/item/edit.php \
	admin/tmpl/items/default.php \
	admin/tmpl/items/emptystate.php \
	admin/tmpl/lend/edit.php \
	admin/tmpl/lends/default.php \
	admin/tmpl/lends/emptystate.php \
	admin/tmpl/tag/edit.php \
	admin/tmpl/taggroup/edit.php \
	admin/tmpl/taggroups/default.php \
	admin/tmpl/taggroups/emptystate.php \
	admin/tmpl/tags/default.php \
	admin/tmpl/tags/emptystate.php \
	admin/tmpl/ysinventory/default.php \
	site/forms/lend.xml \
	site/language/en-GB/com_ysinventory.ini \
	site/src/Controller/DisplayController.php \
	site/src/Controller/LendController.php \
	site/src/Model/BrandModel.php \
	site/src/Model/BrandsModel.php \
	site/src/Model/CategoriesModel.php \
	site/src/Model/CategoryModel.php \
	site/src/Model/ItemModel.php \
	site/src/Model/ItemsModel.php \
	site/src/Model/LendModel.php \
	site/src/Model/LendsModel.php \
	site/src/Model/TagModel.php \
	site/src/Model/TagsModel.php \
	site/src/View/Brand/HtmlView.php \
	site/src/View/Brands/HtmlView.php \
	site/src/View/Categories/HtmlView.php \
	site/src/View/Category/HtmlView.php \
	site/src/View/Item/HtmlView.php \
	site/src/View/Items/HtmlView.php \
	site/src/View/Lend/HtmlView.php \
	site/src/View/Lends/HtmlView.php \
	site/src/View/Tag/HtmlView.php \
	site/src/View/Tags/HtmlView.php \
	site/tmpl/brand/default.php \
	site/tmpl/brand/default.xml \
	site/tmpl/brands/default.php \
	site/tmpl/brands/default.xml \
	site/tmpl/categories/default.php \
	site/tmpl/categories/default.xml \
	site/tmpl/category/default.php \
	site/tmpl/category/default.xml \
	site/tmpl/item/default.php \
	site/tmpl/item/default.xml \
	site/tmpl/items/default.php \
	site/tmpl/items/default.xml \
	site/tmpl/lend/edit.php \
	site/tmpl/lends/default.php \
	site/tmpl/lends/default.xml \
	site/tmpl/tag/default.php \
	site/tmpl/tag/default.xml \
	site/tmpl/tags/default.php \
	site/tmpl/tags/default.xml

JZIP ?= tools/jzip.php
ZIP_LEVEL ?= 9
INSTALL_DIR ?= installation
BUILD_DIR ?= build

RELEASE_NOTES ?= docs/RELEASE.md
# The template lives under tools/ because the root $(EXT_NAME).update.xml is the feed
# that 1.0.0 installs poll (its <updateservers> pointed at this repository). That root
# file is a published descriptor, not a template: after each release, copy the
# generated artifact over it as well as into joomla_update_system/manifests/.
UPDATE_NAME := $(EXT_NAME).update.xml
UPDATE_TEMPLATE := tools/$(UPDATE_NAME)
SHA256_PLACEHOLDER := 0000000000000000000000000000000000000000000000000000000000000000

# `override` is load-bearing. The tag, the ZIP name and the download URL all derive
# from this, and deriving it from the manifest instead of typing it is the whole point
# of `release`. Without `override`, `make release VERSION=x` tags a version the manifest
# never declared - and refusing to package it afterwards does not remove the tag.
override VERSION := $(shell awk -F'[<>]' '/<version>/{print $$3; exit}' $(MANIFEST))
ZIP_NAME := $(EXT_NAME)-v$(subst .,-,$(VERSION)).zip
RELEASE_STAGE := $(BUILD_DIR)/release
DEV_STAGE := $(BUILD_DIR)/dev
RELEASE_ZIP := $(INSTALL_DIR)/release/$(ZIP_NAME)
DEV_ZIP := $(INSTALL_DIR)/dev/$(ZIP_NAME)
UPDATE_ARTIFACT := $(INSTALL_DIR)/release/$(UPDATE_NAME)
DOWNLOAD_URL := https://github.com/$(GITHUB_OWNER)/$(GITHUB_REPO)/releases/download/$(VERSION)/$(ZIP_NAME)

# Every variable that decides the published ZIP bytes or the descriptor. dist_release
# refuses to run unless each one comes from this file: a command-line or environment
# value for any of them (MANIFEST, GITHUB_OWNER, ...) changes what is published
# without changing the tag. INSTALL_DIR and BUILD_DIR only move the output.
RELEASE_VARS := EXT_NAME MANIFEST GITHUB_OWNER GITHUB_REPO PACKAGE_FILES JZIP ZIP_LEVEL \
	UPDATE_NAME UPDATE_TEMPLATE SHA256_PLACEHOLDER ZIP_NAME RELEASE_STAGE RELEASE_ZIP \
	UPDATE_ARTIFACT DOWNLOAD_URL package sha256
# Deferred (=) so `package` and `sha256`, defined further down, are seen. firstword:
# `command line` and `environment override` are two words each.
RELEASE_VARS_OVERRIDDEN = $(strip $(foreach v,$(RELEASE_VARS),$(if $(filter-out file,$(firstword $(origin $(v)))),$(v))))

# No Composer and no automated tests in this repository. These are the gates `release`
# runs before it tags, so they are `:=`: an environment variable of the same name must
# not replace them silently. Defined after the variables they reference.
DEPS_CMD := @echo "No dependencies to install."
TEST_CMD := @echo "No automated tests."
# $(JZIP) is linted although it does not ship: a syntax error in it would otherwise
# surface only after `release` has created the tag.
LINT_CMD := @set -e; \
	for f in $(filter %.php,$(PACKAGE_FILES)) $(JZIP); do php -l "$$f" >/dev/null || exit 1; done; \
	for f in $(filter %.xml,$(PACKAGE_FILES)) $(UPDATE_TEMPLATE); do \
		php -r 'libxml_use_internal_errors(true); if (simplexml_load_file($$argv[1]) === false) { fwrite(STDERR, "FAIL: malformed XML in $$argv[1]\n"); exit(1); }' "$$f" || exit 1; \
	done; \
	echo "lint: ok"

sha256 = $$( { if command -v sha256sum >/dev/null 2>&1; then sha256sum "$(1)"; else shasum -a 256 "$(1)"; fi; } | awk '{print $$1}' | grep -Ex '[0-9a-f]{64}' )

# Package staging directory $(2) into $(1), stamping every entry with epoch $(3).
define package
	@set -e; \
	for f in $(PACKAGE_FILES); do \
		if [ ! -f "$(2)/$$f" ]; then \
			echo "FAIL: $$f is in PACKAGE_FILES but not in $(2)/ - the staged tree is incomplete"; \
			exit 1; \
		fi; \
	done; \
	mkdir -p "$(dir $(1))"; \
	archive="$$(cd "$(dir $(1))" && pwd)/$$(basename "$(1)").part"; stamp="$(3)"; \
	rm -f "$(1).part"; \
	if [ -f "$(JZIP)" ]; then \
		php "$(JZIP)" --level=$(ZIP_LEVEL) "$$archive" "$$stamp" "$(2)" $(PACKAGE_FILES) >/dev/null; \
		how="jzip level $(ZIP_LEVEL)"; \
	else \
		echo "WARNING: jzip not found at $(JZIP) - falling back to zip." >&2; \
		echo "WARNING: the package is NOT reproducible - zip stores this machine's mtimes and host metadata." >&2; \
		( cd "$(2)" && zip -q -X -$(ZIP_LEVEL) "$$archive" $(PACKAGE_FILES) ); \
		how="zip fallback"; \
	fi; \
	mv "$(1).part" "$(1)"; \
	sum="$(call sha256,$(1))"; \
	echo "$(1): $(words $(PACKAGE_FILES)) entries, $$(wc -c < "$(1)") bytes, $$how, sha256 $$sum"
endef

.PHONY: info deps test lint release dist_release dist_dev update_manifest clean

info:
	@echo "Extension:       $(EXT_NAME)"
	@echo "Version:         $(VERSION)"
	@echo "Package files:   $(words $(PACKAGE_FILES)) (PACKAGE_FILES in Makefile)"
	@echo "Release package: $(RELEASE_ZIP)"
	@echo "Dev package:     $(DEV_ZIP)"
	@echo "Update template: $(UPDATE_TEMPLATE)"
	@echo "Update artifact: $(UPDATE_ARTIFACT)"
	@echo "Download URL:    $(DOWNLOAD_URL)"
	@echo "Packager:        $(JZIP) (level $(ZIP_LEVEL))"

deps:
	$(DEPS_CMD)

test:
	$(TEST_CMD)

lint:
	$(LINT_CMD)

release:
	@set -e; \
	dirty="$$(git status --porcelain)" || { echo "FAIL: git status failed - cannot confirm the tree is clean"; exit 1; }; \
	if [ -n "$$dirty" ]; then \
		echo "FAIL: working tree is dirty - commit or stash before tagging"; \
		printf '%s\n' "$$dirty"; \
		exit 1; \
	fi
	@if git rev-parse -q --verify "refs/tags/$(VERSION)" >/dev/null; then \
		echo "FAIL: tag $(VERSION) already exists - bump <version> in $(MANIFEST)"; \
		exit 1; \
	fi
	@if ! awk -v v="$(VERSION)" '/^## /{ if ($$2==v) found=1 } END{ exit !found }' "$(RELEASE_NOTES)"; then \
		echo "FAIL: $(RELEASE_NOTES) has no '## $(VERSION)' heading - write the release notes before tagging"; \
		exit 1; \
	fi
	@$(MAKE) --no-print-directory test
	@$(MAKE) --no-print-directory lint
	@git tag "$(VERSION)"
	@echo "Tagged $(VERSION) - next: make dist_release"

dist_release:
	@if ! git rev-parse -q --verify "refs/tags/$(VERSION)" >/dev/null; then \
		echo "FAIL: no tag $(VERSION) - run 'make release' first"; \
		exit 1; \
	fi
	@# Only the payload files come from the tag. PACKAGE_FILES, the packager, the
	@# compression level and the descriptor template are all read from the checkout,
	@# so the archive is reproducible from the tag only when the checkout IS the tag.
	@set -e; \
	dirty="$$(git status --porcelain)" || { echo "FAIL: git status failed - cannot confirm the tree is clean"; exit 1; }; \
	if [ -n "$$dirty" ]; then \
		echo "FAIL: working tree is dirty - a release must be built from a clean checkout of $(VERSION)"; \
		printf '%s\n' "$$dirty"; \
		exit 1; \
	fi; \
	head="$$(git rev-parse HEAD)"; \
	tagged="$$(git rev-parse "$(VERSION)^{commit}")"; \
	if [ "$$head" != "$$tagged" ]; then \
		echo "FAIL: HEAD is not tag $(VERSION)"; \
		echo "      HEAD $$head"; \
		echo "      tag  $$tagged"; \
		echo "      building here would use this checkout's build inputs against the tag's payload,"; \
		echo "      producing bytes a clean checkout of $(VERSION) cannot reproduce. Check the tag out."; \
		exit 1; \
	fi
	@# Same reasoning for the variables that decide the published bytes. Require origin
	@# `file` for all of RELEASE_VARS: `?=` lets an environment variable through as
	@# `environment`, `make -e` reports `environment override`, and a command-line value
	@# overrides even a `:=` assignment.
	@if [ -n "$(RELEASE_VARS_OVERRIDDEN)" ]; then \
		echo "FAIL: $(RELEASE_VARS_OVERRIDDEN) set from the command line or environment."; \
		echo "      These decide the published bytes and must come from the tagged Makefile."; \
		echo "      Unset them, or change them in the repository instead."; \
		exit 1; \
	fi
	@# The descriptor template is read from the checkout and checked again in
	@# update_manifest, but only after the ZIP is built. Fail before packaging instead.
	@set -e; \
	tpl="$$(awk -F'[<>]' '/<version>/{print $$3; exit}' "$(UPDATE_TEMPLATE)")"; \
	if [ "$$tpl" != "$(VERSION)" ]; then \
		echo "FAIL: $(UPDATE_TEMPLATE) declares $$tpl, not $(VERSION) - bump it with the manifest"; \
		exit 1; \
	fi; \
	if [ "$$(grep -oF "<sha256>$(SHA256_PLACEHOLDER)</sha256>" "$(UPDATE_TEMPLATE)" | wc -l)" -ne 1 ]; then \
		echo "FAIL: $(UPDATE_TEMPLATE) is not a template - <sha256> must be the $(SHA256_PLACEHOLDER) placeholder"; \
		exit 1; \
	fi
	@# The package macro degrades to `zip` when the packager is missing. Tolerable for a
	@# dev package, never for a published one: it would produce a non-reproducible archive
	@# and then stamp a descriptor claiming its checksum.
	@if [ ! -f "$(JZIP)" ]; then \
		echo "FAIL: $(JZIP) is missing - a release package must come from the deterministic"; \
		echo "      packager, never the zip fallback. Restore it before building."; \
		exit 1; \
	fi
	@rm -rf "$(RELEASE_STAGE)"
	@mkdir -p "$(RELEASE_STAGE)"
	@git -c core.autocrlf=false archive "$(VERSION)" | tar -x -C "$(RELEASE_STAGE)"
	@set -e; \
	exported="$$(awk -F'[<>]' '/<version>/{print $$3; exit}' "$(RELEASE_STAGE)/$(MANIFEST)")"; \
	if [ "$$exported" != "$(VERSION)" ]; then \
		echo "FAIL: tag $(VERSION) exports a manifest declaring $$exported"; \
		echo "      the ZIP name, download URL and checksum all come from the WORKING TREE manifest,"; \
		echo "      so packaging this would publish one version under another's name - move the tag"; \
		echo "      or fix <version> before rebuilding."; \
		exit 1; \
	fi
	$(call package,$(RELEASE_ZIP),$(RELEASE_STAGE),$$(git log -1 --format=%ct "$(VERSION)"))
	@rm -rf "$(RELEASE_STAGE)"
	@$(MAKE) --no-print-directory update_manifest

dist_dev:
	@rm -rf "$(DEV_STAGE)"
	@mkdir -p "$(DEV_STAGE)"
	@set -e; \
	for f in $(PACKAGE_FILES); do \
		mkdir -p "$(DEV_STAGE)/$$(dirname "$$f")"; \
		cp "$$f" "$(DEV_STAGE)/$$f"; \
	done
	@sed '/<updateservers>/,/<\/updateservers>/d' "$(DEV_STAGE)/$(MANIFEST)" > "$(DEV_STAGE)/manifest.tmp" \
		&& mv "$(DEV_STAGE)/manifest.tmp" "$(DEV_STAGE)/$(MANIFEST)"
	@# grep exits 2 on an unreadable file, which an `if` cannot tell from "no match" -
	@# an assertion that silently passes when it could not check is worse than none.
	@set -e; \
	rc=0; grep -q "updateservers" "$(DEV_STAGE)/$(MANIFEST)" || rc=$$?; \
	if [ "$$rc" -eq 0 ]; then \
		echo "FAIL: <updateservers> survived in the dev manifest"; \
		exit 1; \
	elif [ "$$rc" -gt 1 ]; then \
		echo "FAIL: cannot read $(DEV_STAGE)/$(MANIFEST) to confirm <updateservers> was stripped"; \
		exit 1; \
	fi
	$(call package,$(DEV_ZIP),$(DEV_STAGE),$$(git log -1 --format=%ct))
	@rm -rf "$(DEV_STAGE)"

update_manifest:
	@set -e; \
	if [ "$$(grep -oF "<sha256>$(SHA256_PLACEHOLDER)</sha256>" "$(UPDATE_TEMPLATE)" | wc -l)" -ne 1 ]; then \
		echo "FAIL: $(UPDATE_TEMPLATE) is not a template - <sha256> must be the $(SHA256_PLACEHOLDER) placeholder"; \
		exit 1; \
	fi; \
	SHA256="$(call sha256,$(RELEASE_ZIP))"; \
	mkdir -p "$(dir $(UPDATE_ARTIFACT))"; \
	awk -v url="$(DOWNLOAD_URL)" -v sha="$$SHA256" '{ \
		if ($$0 ~ /<downloadurl[^>]*>[^<]+<\/downloadurl>/) { \
			sub(/<downloadurl[^>]*>[^<]+<\/downloadurl>/, "<downloadurl type=\"full\" format=\"zip\">" url "</downloadurl>"); \
		} else if ($$0 ~ /<sha256>[^<]+<\/sha256>/) { \
			sub(/<sha256>[^<]+<\/sha256>/, "<sha256>" sha "</sha256>"); \
		} \
		print; \
	}' "$(UPDATE_TEMPLATE)" > "$(UPDATE_ARTIFACT).part"; \
	if [ "$$(grep -oF "<sha256>$$SHA256</sha256>" "$(UPDATE_ARTIFACT).part" | wc -l)" -ne 1 ]; then \
		echo "FAIL: $(UPDATE_ARTIFACT) does not carry exactly one <sha256> for $(RELEASE_ZIP)"; \
		exit 1; \
	fi; \
	if [ "$$(grep -oF '<downloadurl type="full" format="zip">$(DOWNLOAD_URL)</downloadurl>' "$(UPDATE_ARTIFACT).part" | wc -l)" -ne 1 ]; then \
		echo "FAIL: $(UPDATE_ARTIFACT) does not carry exactly one <downloadurl> for $(VERSION)"; \
		exit 1; \
	fi; \
	if [ "$$(grep -oF "<sha256>" "$(UPDATE_ARTIFACT).part" | wc -l)" -ne 1 ] \
		|| [ "$$(grep -oF "<downloadurl" "$(UPDATE_ARTIFACT).part" | wc -l)" -ne 1 ]; then \
		echo "FAIL: $(UPDATE_ARTIFACT) carries a second <sha256> or <downloadurl> this recipe did not write"; \
		exit 1; \
	fi; \
	if [ "$$(awk -F'[<>]' '/<version>/{print $$3; exit}' "$(UPDATE_ARTIFACT).part")" != "$(VERSION)" ]; then \
		echo "FAIL: $(UPDATE_ARTIFACT) declares a version other than $(VERSION)"; \
		exit 1; \
	fi; \
	mv "$(UPDATE_ARTIFACT).part" "$(UPDATE_ARTIFACT)"
	@echo "Wrote $(UPDATE_ARTIFACT)"

clean:
	@rm -rf "$(BUILD_DIR)" "$(INSTALL_DIR)/release" "$(INSTALL_DIR)/dev"
